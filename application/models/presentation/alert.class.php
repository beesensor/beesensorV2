<?php
Class Alert {
    private $type;
    private $header;
    private $msg;

    public function __construct($type, $header, $msg) {
        $this->type = $type;
        $this->header = $header;
        $this->msg = $msg;
    }

    public function toString() {
        $ret = '<div class="row">';
        $ret .= '<div class="col-xs-12">';
        $ret .= '<div class="alert alert-'.$this->type.' alert-dismissible">';
        $ret .= '<button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>';
        $ret .= '<h4>';
            
        switch ($this->type) {
            case "danger":
                $ret .= '<i class="icon fa fa-ban"> </i>';
                break;
            case "info":
                $ret .= '<i class="icon fa fa-info"> </i>';
                break;
            case "warning":
                $ret .= '<i class="icon fa fa-warning"> </i>';
                break;
            case "success":
                $ret .= '<i class="icon fa fa-check"> </i>';
                break; 
        }
        $ret .= $this->header.'</h4>';
        $ret .= $this->msg."</div>";
        $ret .= '</div>';
        $ret .= '</div>';
        
        return $ret;
    }
}