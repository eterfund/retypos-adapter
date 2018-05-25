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
     * @param string $typo          Typo to be fixed
     * @param string $corrected     Correct variant
     * @param string $context       Context of typo
     * @param string $link          Link where the typo exist
     */
    public function fixTypo(string $typo, string $corrected, string $context, string $link) {
        $article = $this->getArticleFromLink($link);

        $this->replaceTypoInArticle($typo, $corrected, $context, $article);

        $this->saveArticle($article);
    }

    /**
     * This method replaces a given typo in article, using the context to a correct
     * variant.
     *
     * @param string $typo          Typo to be replaced
     * @param string $corrected     Correct variant
     * @param string $context       Context where the typo found
     * @param TyposArticle $article Article to fix the typo
     */
    private function replaceTypoInArticle(string $typo, string $corrected, string $context, TyposArticle $article) {

    }
}