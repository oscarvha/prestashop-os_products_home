<?php
/*
* 2007-2015 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2015 PrestaShop SA
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

if (!defined('_PS_VERSION_')) {
    exit;
}
use PrestaShop\PrestaShop\Core\Module\WidgetInterface;
use PrestaShop\PrestaShop\Adapter\Image\ImageRetriever;
use PrestaShop\PrestaShop\Adapter\Product\PriceFormatter;
use PrestaShop\PrestaShop\Core\Product\ProductListingPresenter;
use PrestaShop\PrestaShop\Adapter\Product\ProductColorsRetriever;

include_once(_PS_MODULE_DIR_ . 'os_products_home/src/Model/HomeProduct.php');


class os_products_home extends Module implements WidgetInterface
{
    private $templateFile;

    public function __construct()
    {
        $this->name = 'os_products_home';
        $this->author = 'Oscar Sanchez';
        $this->version = '0.50';

        $this->bootstrap = true;
        parent::__construct();

        $this->displayName = "Productos en la home";
        $this->description = "Home products";
        $this->ps_versions_compliancy = array('min' => '1.7.0.0', 'max' => _PS_VERSION_);

        $this->templateFile = 'module:os_products_home/views/templates/front/home-list.tpl';
    }

    public function install()
    {
        if(parent::install() &&
            $this->registerHook('displayHome')
            && $this->registerHook('header')
            && $this->registerHook('actionProductDelete')
        )
        {
            return $this->createTables();


        }
        return false;
    }

    public function createTables()
    {

        $db = Db::getInstance();

        $sql1 = "CREATE TABLE IF NOT EXISTS " . _DB_PREFIX_ . "os_home_product (
                id_product INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                position INT(10) unsigned NOT NULL DEFAULT 0
                )";


        $sql2 = "CREATE TABLE IF NOT EXISTS " . _DB_PREFIX_ . "os_home_product_lang (
                id_product INT(11) UNSIGNED NOT NULL,
                id_lang INT(10) UNSIGNED NOT NULL,
                name_block VARCHAR(254),
                PRIMARY KEY (id_product,id_lang)
                )";

        return $db->execute($sql1) && $db->execute($sql2);
    }

    public function renderWidget($hookName = null, array $configuration = [])
    {
        if(!$this->isCached($this->templateFile, $this->getCacheId('home-list')))
        {
            $this->smarty->assign($this->getWidgetVariables($hookName, $configuration));
        }

        return $this->fetch($this->templateFile, $this->getCacheId('home-list'));
    }

    public function getWidgetVariables($hookName = null, array $configuration = [])
    {

        $productsForTemplate = $this->getProductsForTemplate();

        return array(

            'productsForTemplate' => $productsForTemplate

        );
    }

    public function getProductsForTemplate()
    {


        $assembler = new ProductAssembler($this->context);

        $presenterFactory = new ProductPresenterFactory($this->context);
        $presentationSettings = $presenterFactory->getPresentationSettings();
        $presenter = new ProductListingPresenter(
            new ImageRetriever(
                $this->context->link
            ),
            $this->context->link,
            new PriceFormatter(),
            new ProductColorsRetriever(),
            $this->context->getTranslator()
        );


        $productsInHome = HomeProduct::getAllProductsHome();

        $products_for_template = [];

        foreach($productsInHome as $rawProduct)
        {
            if(Validate::isLoadedObject(new Product($rawProduct['id_product'])))
            {
                $products_for_template[] = $presenter->present(
                    $presentationSettings,
                    $assembler->assembleProduct($rawProduct),
                    $this->context->language
                );

            }

        }

        return $products_for_template;

    }

    public function getContent()
    {
        $output = null;

        $id_product = Tools::getValue('changePosition');

        if($id_product && $positionDestiny = Tools::getValue('positionDestininy')){

            $newProductHome = new HomeProduct($id_product);
            if($newProductHome->position != $positionDestiny){

                $positionChange = HomeProduct::changePosition($id_product, $newProductHome->position, $positionDestiny);

            }
        }

        if(Tools::getValue('addProduct')){

            $maxPosition = HomeProduct::getMaxPosition();

            if(isset($maxPosition[0]['position'])){

                $maxPosition = $maxPosition[0]['position'];

            }else{
                $maxPosition = 0;
            }

            $id_product = Tools::getValue('addProduct');

            if(!HomeProduct::productExist($id_product) && Product::existsInDatabase($id_product, 'product')){
                $newProductHome = new HomeProduct($id_product);
                $newProductHome->id_product = $id_product;
                $newProductHome->position = $maxPosition + 1;

                if($newProductHome->save()){

                    $this->displayConfirmation($this->l('Producto añadido a la home correctamente'));

                }else{

                    $this->displayError($this->l('El producto no se ha podido añadir'));
                }
            }

        }

        if(Tools::getValue('deleteProduct')){

            $id_product = Tools::getValue('deleteProduct');
            if(HomeProduct::productExist($id_product) && Product::existsInDatabase($id_product, 'product')){

                $newProductHome = new HomeProduct($id_product);
                if($newProductHome->delete()){
                    $positionDelete = $newProductHome->position;
                    if($positionDelete > 0)
                    {
                        $delete = HomeProduct::orderPositionAfterDelete($positionDelete);
                    }
                    $this->displayConfirmation($this->l('Producto borrado de la home correctamente'));

                }else{
                    $this->displayError($this->l('El producto no se ha podido borrar de la home'));
                }

            }
        }


        $output .= $this->renderList();
        $this->context->controller->addJqueryUI('ui.sortable');

        return $output;

    }


    public function renderList()
    {

        $products = Product::getProducts($this->context->language->id, 0, 100, 'name', 'ASC', false, true);


        $productsInHome = HomeProduct::getAllProductsHome();

        $productsHome = array();
        $positions = array();

        foreach($productsInHome as $productHome)
        {

            $newProduct = new Product($productHome['id_product']);

            array_push($positions, $productHome['position']);
            array_push($productsHome, $newProduct);

            /**Eliminamos los products existentes en el slider del array de productos **/
            $key = array_search($productHome['id_product'], array_column($products, 'id_product'));
            if($key)
            {
                unset($products[$key]);
            }

        }


        $this->context->smarty->assign(
            array(
                'link' => $this->context->link,
                'products' => $products,
                'productsHome' => $productsHome,
                'lang' => $this->context->language->id,
                'positions' => $positions
            )
        );

        return $this->display(__FILE__, '/views/templates/admin/admin-render.tpl');
    }

    public function hookActionProductDelete($params){

        return HomeProduct::deleteByIdProduc($params['id_product']);


    }

}