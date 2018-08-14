<?php
/**
 * Created by KhoiLe - mr.vjcspy@gmail.com
 * Date: 28/03/2018
 * Time: 10:37
 */
namespace SM\XRetail\Model;

use Magento\Config\Model\Config\CommentInterface;

class Comment implements CommentInterface
{
    private $helper;
    public function __construct(
        \SM\XRetail\Helper\Data $helper
    ) {
        $this->helper          = $helper;
    }

    public function getCommentText($elementValue)  //the method has to be named getCommentText
    {
        $text = $this->helper->getCurrentVersion();
        return "API Version: ".$text;
    }
}