<?php
/**
 * Created by PhpStorm.
 * User: ambulance
 * Date: 25.05.18
 * Time: 19:47
 */

namespace Etersoft\Typos;

/**
 * Class TyposArticle
 *
 * An article object with a text and id
 *
 * @package Etersoft\Typos
 */
class TyposArticle
{
    public $id;
    public $text;

    /**
     * TyposArticle constructor.
     * @param $id   integer Article id
     * @param $text string Article text
     */
    public function __construct(integer $id, string $text)
    {
        $this->id = $id;
        $this->text = $text;
    }
}