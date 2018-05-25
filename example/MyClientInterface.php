<?php
namespace My;

use Etersoft\Typos\TyposArticle;
use Etersoft\Typos\TyposClientInterface;

/**
 * Client interface example implementation
 */
class MyClientInterface extends TyposClientInterface {

    /**
     * Should return an article text for a provided article link.
     *
     * @param string $link A link to a article. User should define an article id.
     *
     * @return \Etersoft\Typos\TyposArticle
     */
    protected function getArticleFromLink(string $link)
    {
        return new TyposArticle(0, "");
    }

    /**
     * Should persist a provided article in database.
     *
     * @param \Etersoft\Typos\TyposArticle $article Article
     * @return void
     */
    protected function saveArticle(TyposArticle $article)
    {
    }
}