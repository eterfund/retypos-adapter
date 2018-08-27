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
        $response = [
            "errorCode" => 200,
            "message" => "success",
        ];

        try {
            $article = $this->getArticleFromLink($link);
            $this->replaceTypoInArticle($typo, $corrected, $context, $article);
            $this->saveArticle($article);
        } catch (\Exception $e) {
            $response["errorCode"] = $e->getCode();
            $response["message"] = $e->getMessage();
            return $response;
        }

        return $response;
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
     * @throws \Exception
     *  404 - Typo does not exist
     *  405 - Context has been changed
     */
    public function replaceTypoInArticle(string $typo, string $corrected, string $context, TyposArticle $article) {
        // Strip all tags from text
        $text = strip_tags($article->text);

        // Find all typos in text, capture an offset of each typo
        $typos = [];
        preg_match_all("#{$typo}#", $text, $typos, PREG_OFFSET_CAPTURE);
        $typos = $typos[0];

        if (!isset($typos[0])) {
            // Check for already fixed typo
            preg_match_all("#{$corrected}#", $text, $typos, PREG_OFFSET_CAPTURE);

            if (isset($typos[0])) {
                throw new \Exception("Already fixed", 208);
            }

            throw new \Exception("Typo not found", 404);
        }

        // Find a context in text, capture it offset
        $contextMatch = [];
        preg_match_all("#{$context}#", $text, $contextMatch, PREG_OFFSET_CAPTURE);

        // If a context was changed then report an error,
        // cannot locate typo in a new context, must be
        // fixed manually
        if (!isset($contextMatch[0])) {
            throw new \Exception("Context not found", 405);
        }

        $contextMatch = $contextMatch[0];
        $contextOffset = $contextMatch[0][1];

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
        $article->text = preg_replace_callback("#{$typo}#",
            function($match) use(&$index, $indexOfTypo, $corrected) {
                $index++;
                if (($index - 1) == $indexOfTypo) {
                    return $corrected;
                }

                return $match[0];
            },
            $article->text);
    }

}