<?php
/**
 * Created by PhpStorm.
 * User: ambulance
 * Date: 25.05.18
 * Time: 19:39
 */

namespace Etersoft\Typos;

/**
 * Class TyposClientInterface
 *
 * Interface for the TyposClient. Must be implemented by a user
 * and passed to the TyposClient constructor during initialization process.
 *
 * @package Etersoft\Typos
 */
abstract class TyposClientInterface
{
    /**
     * Should return an article text for a provided article link.
     *
     * @param string $link A link to a article. User should define an article id.
     *
     * @return TyposArticle
     */
    protected abstract function getArticleFromLink(string $link);

    /**
     * Should persist a provided article in database.
     *
     * @param TyposArticle $article Article
     * @return void
     */
    protected abstract function saveArticle(TyposArticle $article);

    /**
     * Should return an article id from provided article url
     *
     * @param string $link Article URL
     * @return integer Article ID
     *
     * @throws \InvalidArgumentException If id cannot be extracted from link
     */
    protected abstract function getArticleIdFromLink(string $link);

    /**
     * Should return an edit link for an article with a given id
     *
     * @param int $id Article ID
     * @return string Article edit URL
     *
     * @throws \Exception If an article with a given id has not been found
     */
    protected abstract function getArticleEditLink(int $id);

    /**
     * Fixes a typo in an article from a $link url. Uses a context while
     * fixing to determine a typo position.
     *
     * @param string $typo Typo to be fixed
     * @param string $corrected Correct variant
     * @param string $context Context of typo
     * @param string $link Link where the typo exist
     *
     * @return array Array contains error code and optional message
     */
    public function fixTypo(string $typo, string $corrected, string $context, string $link) {
        try {
            $article = $this->getArticleFromLink($link);
            $this->replaceTypoInArticle($typo, $corrected, $context, $article);
            $this->saveArticle($article);
        } catch (\Exception $e) {
            return $this->getErrorMessage($e->getCode(), $e->getMessage());
        }

        return $this->getSuccessMessage("success");
    }

    /**
     * Constructs a success message
     *
     * @param mixed $message Some data to send to the requesting server
     * @return array Success response
     */
    private function getSuccessMessage($message) {
        return [
          "errorCode" => 200,
          "message" => $message
        ];
    }

    /**
     * Constructs a error message
     *
     * @param int $errorCode
     * @param string $message Error description
     * @return array Error response
     */
    private function getErrorMessage(int $errorCode, string $message) {
        return [
            "errorCode" => $errorCode,
            "message" => $message
        ];
    }

    /**
     * Returns an edit link for a given article link
     *
     * @param string $link Article link
     * @return array Response array. If errorCode == 200 then message contains an edit link
     */
    public function getEditLink(string $link) {
        try {
            // May throw InvalidArgumentException
            $id = $this->getArticleIdFromLink($link);

            // May throw Exception (if article has not been found)
            return $this->getSuccessMessage($this->getArticleEditLink($id));
        } catch (\Exception $e) {
            error_log(`[TyposClientInterface] [getEditLink] Failed to get edit link: {$e->getMessage()}`);
            return $this->getErrorMessage(500, "Failed to get an edit link: {$e->getMessage()}");
        }
    }

    /**
     * This method replaces a given typo in article, using the context to a correct
     * variant.
     *
     * @param string $typo Typo to be replaced
     * @param string $corrected Correct variant
     * @param string $context Context where the typo found
     * @param TyposArticle $article Article to fix the typo
     *
     * @throws \Exception 404 - Typo does not exist
     */
    private function replaceTypoInArticle(string $typo, string $corrected, string $context, TyposArticle $article) {
        $lastException = null;

        $isContextNotFound = false;

        // Replace -- to - in context string
        // BUG# 12799
        $context = str_replace("\xe2\x80\x94", "-", $context);

        // BUG# 12799 Need to change quotes and preg quote
        $context = preg_quote(preg_replace('#«([^«]*)»#', '"$1"', $context));

        // Trying to replace typo in text
        try {
            error_log("Trying to find a typo in article text...");
            $article->text = $this->replaceTypoInText($typo, $corrected, $context, $article->text);
            return;
        } catch (\Exception $e) {
            error_log($e->getMessage());

            if ($e->getCode() != 404 && $e->getCode() != 405) {
                throw $e;
            }

            // If context was not found in text then remember this
            // and after all search if we not found a typo, then throw
            // 405 exception and not 404
            if ($e->getCode() == 405) {
                $isContextNotFound = true;
            }
        }

        // Trying to replace typo in title
        try {
            error_log("Trying to find a typo in article title...");
            $article->title = $this->replaceTypoInText($typo, $corrected, $context, $article->title);
            return;
        } catch (\Exception $e) {
            error_log($e->getMessage());

            if ($e->getCode() != 404 && $e->getCode() != 405) {
                throw $e;
            }

            if ($e->getCode() == 405) {
                $isContextNotFound = true;
            }
        }

        // Trying to replace typo in subtitle
        try {
            error_log("Trying to find a typo in article subtitle...");
            $article->subtitle = $this->replaceTypoInText($typo, $corrected, $context, $article->subtitle);
        } catch (\Exception $e) {
            if ($e->getCode() == 404 && $isContextNotFound) {
                throw new \Exception("Context not found", 405);
            }

            throw $e;
        }
    }


    /**
     * Finds and replaces a typo in a given text using provided context.
     * If typo has not been found then exception will be thrown
     *
     * @param string $typo
     * @param string $corrected
     * @param string $context
     * @param $text
     *
     * @return string       Text with typo replaced by corrected
     * @throws \Exception If something goes wrong
     */
    private function replaceTypoInText(string $typo, string $corrected, string $context, $text) {
        // Strip all tags from text
        // Also copy input string
        $originalText = $text;
        $text = strip_tags($text);

        // BUG# 13121 
        $typo = str_replace("\xc2\xa0", " ", $typo);
        $corrected = str_replace("\xc2\xa0", " ", $corrected);
        $context = preg_replace("\xc2\xa0", "", $context);

        // Find all typos in text, capture an offset of each typo
        $typos = [];
        preg_match("#{$typo}#", $text, $typos, PREG_OFFSET_CAPTURE);

        if (count($typos) == 0) {
            // Check for already fixed typo
            preg_match("#{$corrected}#", $text, $typos, PREG_OFFSET_CAPTURE);

            if (count($typos) != 0) {
                throw new \Exception("Already fixed", 208);
            }

            throw new \Exception("Typo not found", 404);
        }

        // Find a context in text, capture it offset
        $contextMatch = [];
        preg_match("#{$context}#", $text, $contextMatch, PREG_OFFSET_CAPTURE);

        // If a context was changed then report an error,
        // cannot locate typo in a new context, must be
        // fixed manually
        if (count($contextMatch) == 0) {
            throw new \Exception("Context not found", 405);
        }

        $contextMatch = $contextMatch[0];
        $contextOffset = $contextMatch[1];

        // Find a concrete typo that we want to fix
        $indexOfTypo = null;
        foreach ($typos as $index => $match) {
            $typoOffset = $match[1];
            if ($typoOffset >= $contextOffset) {
                $indexOfTypo = $index;
                break;
            }
        }

        // Fix a match with index = $indexOfTypo
        $index = 0;
        return preg_replace_callback("#{$typo}#",
            function($match) use(&$index, $indexOfTypo, $corrected) {
                $index++;
                if (($index - 1) == $indexOfTypo) {
                    return $corrected;
                }

                return $match[0];
            },
            $originalText);
    }

}