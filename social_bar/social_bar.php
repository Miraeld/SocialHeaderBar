<?php
/**
* 2018 Pimclick - Gaël ROBIN
*
*
*  @author    Gaël ROBIN <gael@luxury-concept.com>
*  @copyright 2018 Pimclick
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*
*/

if (!defined('_PS_VERSION_')) {
    exit;
}

class Social_bar extends Module
{
    protected $config_form = false;

    public function __construct()
    {
        $this->name = 'social_bar';
        $this->tab = 'front_office_features';
        $this->version = '1.0.0';
        $this->author = 'Gaël ROBIN';
        $this->need_instance = 1;

        /**
         * Set $this->bootstrap to true if your module is compliant with bootstrap (PrestaShop 1.6)
         */
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Header Social Bar');
        $this->description = $this->l('Social bar on top of header ');

        $this->confirmUninstall = $this->l('Are you sure you want to uninstall the module');

        $this->ps_versions_compliancy = array('min' => '1.7', 'max' => _PS_VERSION_);
    }

    /**
     * Don't forget to create update methods if needed:
     * http://doc.prestashop.com/display/PS16/Enabling+the+Auto-Update
     */
    public function install()
    {
        Configuration::updateValue('SOCIAL_BAR_LIVE_MODE', false);

        include(dirname(__FILE__).'/sql/install.php');

        return parent::install() &&
            $this->registerHook('header') &&
            $this->registerHook('backOfficeHeader') &&
            $this->registerHook('displaySocialHeader');
    }

    public function uninstall()
    {
        Configuration::deleteByName('SOCIAL_BAR_LIVE_MODE');

        include(dirname(__FILE__).'/sql/uninstall.php');

        return parent::uninstall();
    }

    /**
     * Load the configuration form
     */
    public function getContent()
    {
        /**
         * If values have been submitted in the form, process.
         */
        if (((bool)Tools::isSubmit('submitSocial_barModule')) == true) {
            $this->postProcess();
        }

        $this->context->smarty->assign('module_dir', $this->_path);

        $output = $this->context->smarty->fetch($this->local_path.'views/templates/admin/configure.tpl');

        return $output.$this->renderForm();
    }

    /**
     * Create the form that will be displayed in the configuration of your module.
     */
    protected function renderForm()
    {
        $helper = new HelperForm();

        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->module = $this;
        $helper->default_form_language = $this->context->language->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);

        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitSocial_barModule';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
            .'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');

        $helper->tpl_vars = array(
            'fields_value' => $this->getConfigFormValues(), /* Add values for your inputs */
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        );

        return $helper->generateForm(array($this->getConfigForm()));
    }

    /**
     * Create the structure of your form.
     */
    protected function getConfigForm()
    {
        return array(
            'form' => array(
                'legend' => array(
                'title' => $this->l('Social Bar Header Settings'),
                'icon' => 'icon-cogs',
                ),
                'input' => array(
                    array(
                        'col' => 3,
                        'type' => 'text',
                        'prefix' => '<i class="icon icon-facebook"></i>',
                        'desc' => $this->l('Enter a valid Facebook URL, if you leave it blank it will not be displayed'),
                        'name' => 'SOCIAL_BAR_FACEBOOK_URL',
                        'label' => $this->l('Facebook URL'),
                    ),
                    array(
                        'col' => 3,
                        'type' => 'text',
                        'prefix' => '<i class="icon icon-youtube"></i>',
                        'desc' => $this->l('Enter a valid Youtube URL, if you leave it blank it will not be displayed'),
                        'name' => 'SOCIAL_BAR_YOUTUBE_URL',
                        'label' => $this->l('YouTube URL'),
                    ),
                    array(
                        'col' => 3,
                        'type' => 'text',
                        'prefix' => '<i class="icon icon-instagram"></i>',
                        'desc' => $this->l('Enter a valid Instagram URL, if you leave it blank it will not be displayed'),
                        'name' => 'SOCIAL_BAR_INSTAGRAM_URL',
                        'label' => $this->l('Instagram URL'),
                    ),
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                ),
            ),
        );
    }

    /**
     * Set values for the inputs.
     */
    protected function getConfigFormValues()
    {
        return array(
            'SOCIAL_BAR_FACEBOOK_URL' => Configuration::get('SOCIAL_BAR_FACEBOOK_URL'),
            'SOCIAL_BAR_YOUTUBE_URL' => Configuration::get('SOCIAL_BAR_YOUTUBE_URL'),
            'SOCIAL_BAR_INSTAGRAM_URL' => Configuration::get('SOCIAL_BAR_INSTAGRAM_URL'),
        );
    }

    /**
     * Save form data.
     */
    protected function postProcess()
    {
        $form_values = $this->getConfigFormValues();

        foreach (array_keys($form_values) as $key) {
            Configuration::updateValue($key, Tools::getValue($key));
            if (!empty(Tools::getValue($key))) {
              $allUrls[$key] = Tools::getValue($key);
            } else {
              $allUrls[$key] = " ";
            }
        }


        $sql = array();
        $values = '(\'1\',\''.$allUrls['SOCIAL_BAR_FACEBOOK_URL'].'\',\''.$allUrls['SOCIAL_BAR_YOUTUBE_URL'].'\',\''.$allUrls['SOCIAL_BAR_INSTAGRAM_URL'].'\')';
        $sql[] = 'UPDATE `'._DB_PREFIX_.'social_bar`
                  SET facebook_url = \''. $allUrls['SOCIAL_BAR_FACEBOOK_URL'] .'\', youtube_url =\''.$allUrls['SOCIAL_BAR_YOUTUBE_URL'].'\', instagram_url = \''.$allUrls['SOCIAL_BAR_INSTAGRAM_URL'].'\'
                    WHERE id_social_bar = 1;';
        $this->social_bar_succ = false;
        foreach ($sql as $query) {
            if (Db::getInstance()->execute($query) == false) {
                $this->social_bar_succ = false;
                return false;
            } else {
              $this->social_bar_succ = true;
            }
        }

        if ($this->social_bar_succ) {

        ?>
          <script>
            window.onload = function () {
              var success = document.getElementById('social_bar_success');
              success.style.display = 'block';
            }
          </script>
      <?php
      } else {?>
        <script>

        window.onload = function () {
          var error = document.getElementById('social_bar_error');
          error.style.display = 'block';

        }
        </script>
        <?php
      }
    }
    public function hookBackOfficeHeader()
    {
        if (Tools::getValue('module_name') == $this->name) {
            $this->context->controller->addJS($this->_path.'views/js/back.js');
            $this->context->controller->addCSS($this->_path.'views/css/back.css');
        }
    }


    public function hookHeader()
    {
        $this->context->controller->addJS($this->_path.'/views/js/front.js');
        $this->context->controller->addCSS($this->_path.'/views/css/front.css');
    }

    public function hookdisplaySocialHeader()
    {
      $test = $this->getDbInfo()[0];
      // var_dump($test);

      $this->context->smarty->assign('facebook_url', $test['facebook_url']);
      $this->context->smarty->assign('youtube_url', $test['youtube_url']);
      $this->context->smarty->assign('instagram_url', $test['instagram_url']);

      $this->context->smarty->assign('module_dir', $this->_path);
      // Load the template front/front.tpl

      $output = $this->context->smarty->fetch($this->local_path.'views/templates/front/front.tpl');

      return $output;
    }

    public function getDbInfo() {
      $sql = new DbQuery();
      $sql->select('*');
      $sql->from('social_bar', 'a');
      $sql->where('a.id_social_bar = 1');
      return Db::getInstance()->executeS($sql);
    }
}
