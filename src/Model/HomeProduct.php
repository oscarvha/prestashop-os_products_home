<?php
/**
 * Created by PhpStorm.
 * User: Usuario
 * Date: 08/12/2017
 * Time: 11:59
 */

class HomeProduct extends ObjectModel
{
    public $position;
    public $id_product;

    public static $definition = array(
        'table' => 'os_home_product',
        'primary' => 'id_product',
        'multilang' => false,
        'fields' => array(
             'id_product' =>  array('type' => self::TYPE_INT, 'validate' => 'isunsignedInt', 'required' => false),
            'position' => array('type' => self::TYPE_INT, 'validate' => 'isunsignedInt', 'required' => false),
        )
    );


    /**
 * @param $idProduct
 * @return array|bool|null|object
 */
    public static function productExist($idProduct)
    {

        $req = 'SELECT ams.`id_product` as id_product
                FROM `' . _DB_PREFIX_ . 'os_home_product` ams
                WHERE ams.`id_product` = ' . (int)$idProduct;

        $row = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow($req);
        return ($row);
    }



    /**
     * @return array|bool|null|object
     */
    public static function getAllProductsHome()
    {

        $req = 'SELECT *
                FROM `' . _DB_PREFIX_ . 'os_home_product` ams
                 ORDER BY position ASC';

        $row = Db::getInstance()->executeS($req);
        return ($row);
    }


    public static function getMaxPosition(){


        $req = 'SELECT MAX(ams.`position`) as position
                FROM `' . _DB_PREFIX_ . 'os_home_product` ams';

        $row = Db::getInstance()->executeS($req);
        return ($row);

    }


    public static function orderPositionAfterDelete($positionDelete){


        $req = 'UPDATE '._DB_PREFIX_.'os_home_product SET position = position-1
                WHERE position > '.(int) $positionDelete;

        $row = Db::getInstance()->execute($req);
        return ($row);

    }



    public static function changePosition($idProduct,$currentPosition, $positionDestiny){

        $db = Db::getInstance();


        if($currentPosition < $positionDestiny){

            $req = 'UPDATE '._DB_PREFIX_.'os_home_product SET position = position-1
                WHERE position >  '.(int) $currentPosition. ' AND position <=  '.(int)$positionDestiny;


            $rowTwo = $db->execute($req);
        }else{

            $req = 'UPDATE '._DB_PREFIX_.'os_home_product SET position = position+1
                WHERE  position >='.(int)$positionDestiny.' AND position <  '.(int) $currentPosition;
            $rowTwo = $db->execute($req);
        }



        $req = 'UPDATE '._DB_PREFIX_.'os_home_product SET position ='.(int)$positionDestiny.'
                WHERE id_product = '.(int) $idProduct;

        $rowOne = $db->execute($req);


        return ($rowOne) && ($rowTwo);

    }


    /**
     * @param $idProduct
     * @return array|bool|null|object
     */
    public static function deleteByIdProduc($idProduct)
    {
        $req = 'DELETE    FROM `' . _DB_PREFIX_ . 'os_home_product` WHERE `id_product` = ' . (int)$idProduct;
        return Db::getInstance(_PS_USE_SQL_SLAVE_)->execute($req);
    }


}