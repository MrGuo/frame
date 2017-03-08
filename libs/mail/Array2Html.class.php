<?php
namespace Libs\Mail;

class Array2Html {

    private $data = array();

    public function setParam($data) {
        $this->data = $data;
        return $this;
    }

    public function transfer() {
        reset($this->data);
        $content = '<table border="1" bordercolor="#a0c6e5" style="border-collapse:collapse;">';
        $first = current($this->data);
        $head = count($first);
        $content .= "<tr>";
        foreach ($first as $key => $value) {
            $content .= "<th>{$key}</th>";
        }
        $content .= "</tr>";
        foreach ($this->data as $arrLine) {
            if (empty($arrLine)) {
                continue;
            }
            $content .= "<tr>";
            foreach ($arrLine as $data) {
                $content .= '<td style="border: solid 1px #a0c6e5; height: 20px;">' . $data . '</td>';
            }
            $content .= "</tr>";
        }
        $content .= "</table>";
        return $content;
    }
}
