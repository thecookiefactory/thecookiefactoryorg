<?php

if (!isset($r_c)) header("Location: /notfound.php");

require_once str_repeat("../", $r_c) . "inc/classes/dtime.class.php";

/**
 * forum category class
 *
 */
class forumcategory {

    /**
     * variables
     *
     * @var $id int
     * @var $name string
     * @var $longname string
     * @var $hexcode string
     * @var $hoverhexcode string
     * @var $date object
     */
    protected $id           = null;
    protected $name         = null;
    protected $longname     = null;
    protected $hexcode      = null;
    protected $hoverhexcode = null;
    protected $date         = null;

    public function __construct($id = null) {

        global $con;

        if ($id != null) {

            try {

                $squery = $con->prepare("SELECT * FROM `forumcategories` WHERE `forumcategories`.`id` = :id");
                $squery->bindValue("id", $id, PDO::PARAM_INT);
                $squery->execute();

            } catch (PDOException $e) {

                echo "An error occured while trying to fetch data to the class. (" . $e->getMessage() . ")";

            }

            if ($squery->rowCount() == 1) {

                $srow = $squery->fetch();

                $this->id           = $srow["id"];
                $this->name         = $srow["name"];
                $this->longname     = $srow["longname"];
                $this->hexcode      = $srow["hexcode"];
                $this->hoverhexcode = $srow["hoverhexcode"];
                $this->date         = new dtime($srow["date"]);

            } else {

                echo "Could not find a category with the given id.";

            }

        }

    }

    public function tableBox() {

        ?>
        <td class='forums-entry-category forums-category-<?php echo $this->getName(); ?>'>
            <a class='forums-entry-category-text' href='/forums/category/<?php echo $this->getId(); ?>'>

                <?php echo $this->getName(); ?>

            </a>
        </td>
        <?php

    }

    public function getId() {

        return $this->id;

    }

    public function getName() {

        return $this->name;

    }

    public function getLongName() {

        return $this->longname;

    }

    public function isReal() {

        return ($this->id != null);

    }

}

?>
