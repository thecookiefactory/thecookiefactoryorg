<?php

if (!isset($r_c)) header("Location: /notfound.php");

/**
 * date & time class
 *
 * function longago
 * (line 16)
 *
 * function display
 * (line 56)
 */
class dtime extends DateTime {

    protected function longago() {

        $diff = time() - $this->getTimestamp();

        if ($diff < 10) {

            return "just now";

        } else if ($diff < 60) {

            return $diff . " seconds ago";

        } else if ($diff < 120) {

            return "a minute ago";

        } else if ($diff < 60*60) {

            return ((int)($diff/60)) . " minutes ago";

        } else if ($diff < 60*60*2) {

            return "an hour ago";

        } else if ($diff < 60*60*24) {

            return ((int)($diff/(60*60))) . " hours ago";

        } else if ($diff < 2*60*60*24) {

            return "a day ago";

        } else {

            return ((int)($diff/(60*60*24))) . " days ago";

        }

    }

    public function display() {

        return "<time datetime='" . date(DATE_W3C, $this->getTimestamp()) . "' title='" . date("Y-m-d H:i \C\E\T", $this->getTimestamp()) . "'>" . $this->longago() . "</time>";

    }

}
