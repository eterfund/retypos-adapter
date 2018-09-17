<?php
namespace My;

use Etersoft\Typos\TyposArticle;
use Etersoft\Typos\TyposClientInterface;

/**
 * Client interface example implementation
 */
class MyClientInterface extends TyposClientInterface {

    private $baseUrl = "https://some-site.org";
    private $editPath = "/edit?article=";

    /**
     * Should return an article text for a provided article link.
     *
     * @param string $link A link to a article. User should define an article id.
     *
     * @return \Etersoft\Typos\TyposArticle
     */
    protected function getArticleFromLink(string $link)
    {
        return new TyposArticle(0, "", "", "");
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

    /**
     * Should return an article id from provided article url
     *
     * @param string $link Article URL
     * @return integer Article ID
     *
     * @throws \InvalidArgumentException If id cannot be extracted from link
     */
    protected function getArticleIdFromLink(string $link)
    {
        // $link = https://some-site.org/?article=$link

        $query = parse_url($link, PHP_URL_QUERY);

        $params = [];
        parse_str($query, $params);

        // Provide all checks needed
        if (count($params) === 0) {
            throw new \InvalidArgumentException();
        }

        if (!isset($params["article"])) {
            throw new \InvalidArgumentException();
        }

        if (!is_numeric($params["article"])) {
            throw new \InvalidArgumentException();
        }

        // Return article id
        return $params["article"];
    }

    /**
     * Should return an edit link for an article with a given id
     *
     * @param int $id Article ID
     * @return string Article edit URL
     */
    protected function getArticleEditLink(int $id)
    {
        // https://some-site.org/edit?article=$id
        return `{$this->baseUrl}{$this->editPath}$id`;
    }
}