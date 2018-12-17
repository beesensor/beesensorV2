<?php
Class BreadCrumb {
    private $arrBreadCrumbItems = null;

    public function __construct() {  }

    public function addBreadCrumbItem($text, $href, $class) {
        $breadCrumbItem = new BreadCrumbItem($text, $href, $class);
        if ($this->arrBreadCrumbItems==null) {
            $this->arrBreadCrumbItems=array();
        }
        $this->arrBreadCrumbItems[]=$breadCrumbItem;
    }

    public function toString() {
        if ($this->arrBreadCrumbItems==null) {
            return "";
        } else {
            $stRet = "";
            foreach ($this->arrBreadCrumbItems as $breadCrumbItem) {
                $stRet .= $breadCrumbItem->toString();
            }
            return $stRet;
        }
    }
}

Class BreadCrumbItem {
    private $text;
    private $href;
    private $class;

    public function __construct($text, $href, $class) {
        $this->text  = $text;
        $this->href  = $href;
        $this->class = $class;
    }

    public function toString() {
        $stRet = "<li";
        if (($this->hRef==null) || ($this->hRef=="")) {
            $stRet .= ' class="active">'.$this->text.'</li>';
        } else {
            $stRet .= '><a href="'.$this->hRef.'"><i class="'.$this->class.'"> </i> '.$this->text.'</a>';
        }
        $stRet .= '</li>';
        return $stRet;
    }
}