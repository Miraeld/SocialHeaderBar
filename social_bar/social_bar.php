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
        $this->version = '1.3.0';
        $this->author = 'Gaël ROBIN';
        $this->need_instance = 1;

        /**
         * Set $this->bootstrap to true if your module is compliant with bootstrap (PrestaShop 1.6)
         */
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Header Social Bar');
        $this->description = $this->l('Social bar custom displayed on top of the page over main Menu.');

        $this->confirmUninstall = $this->l('Are you sure you want to uninstall the module');

        $this->ps_versions_compliancy = array('min' => '1.7', 'max' => _PS_VERSION_);
    }

    /**
     * Don't forget to create update methods if needed:
     * http://doc.prestashop.com/display/PS16/Enabling+the+Auto-Update
     */
    public function install()
    {
        include(dirname(__FILE__).'/sql/install.php');
        $configInit = $this->getDbInfo()[0];
        Configuration::updateValue('SOCIAL_BAR_FACEBOOK_URL', $configInit['facebook_url']);
        Configuration::updateValue('SOCIAL_BAR_YOUTUBE_URL', $configInit['youtube_url']);
        Configuration::updateValue('SOCIAL_BAR_INSTAGRAM_URL', $configInit['instagram_url']);
        Configuration::updateValue('SOCIAL_BAR_LINE_URL', $configInit['line_id']);

        return parent::install() &&
            $this->registerHook('header') &&
            $this->registerHook('backOfficeHeader') &&
            $this->registerHook('displaySocialHeader');
    }

    public function uninstall()
    {
        Configuration::deleteByName('SOCIAL_BAR_FACEBOOK_URL');
        Configuration::deleteByName('SOCIAL_BAR_YOUTUBE_URL');
        Configuration::deleteByName('SOCIAL_BAR_INSTAGRAM_URL');
        Configuration::deleteByName('SOCIAL_BAR_LINE_URL');

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
                  'title' => $this->l('Social Header Bar Settings'),
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
                        'placeholder' => 'https://facebook.com',
                    ),
                    array(
                        'col' => 3,
                        'type' => 'text',
                        'prefix' => '<i class="icon icon-youtube"></i>',
                        'desc' => $this->l('Enter a valid Youtube URL, if you leave it blank it will not be displayed'),
                        'name' => 'SOCIAL_BAR_YOUTUBE_URL',
                        'label' => $this->l('YouTube URL'),
                        'placeholder' => 'https://youtube.com',
                    ),
                    array(
                        'col' => 3,
                        'type' => 'text',
                        'prefix' => '<i class="icon icon-instagram"></i>',
                        'desc' => $this->l('Enter a valid Instagram URL, if you leave it blank it will not be displayed'),
                        'name' => 'SOCIAL_BAR_INSTAGRAM_URL',
                        'label' => $this->l('Instagram URL'),
                        'placeholder' => 'https://instagram.com',
                    ),
                    array(
                        'col' => 3,
                        'type' => 'text',
                        'prefix' => '<i class="icon icon-asterisk"></i>',
                        'desc' => $this->l('Enter a valid Line URL/ID, if you leave it blank it will not be displayed'),
                        'name' => 'SOCIAL_BAR_LINE_URL',
                        'label' => $this->l('Line URL/ID'),
                        'placeholder' => 'Line URL',
                    ),
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                    'name' => 'submit_form',
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
            'SOCIAL_BAR_LINE_URL' => Configuration::get('SOCIAL_BAR_LINE_URL'),
        );
    }

    /**
     * Save form data.
     */
    protected function postProcess()
    {
      if (Tools::isSubmit('submit_form')) {
        $form_values = $this->getConfigFormValues();

        foreach (array_keys($form_values) as $key) {
            Configuration::updateValue($key, Tools::getValue($key));
            if (!empty(Tools::getValue($key))) {
              $allUrls[$key] = Tools::getValue($key);
            } else {
              $allUrls[$key] = " ";
            }
        }

        $this->notificationDisplay($this->saveDb($allUrls));

      }
    }

    private function notificationDisplay($show) {
      if ($show) {
        ?>
            <script>
              window.onload = function () {
                var success = document.getElementById('social_bar_success');
                success.style.display = 'block';
              }
            </script>
        <?php
      } else {
        ?>
          <script>

          window.onload = function () {
            var error = document.getElementById('social_bar_error');
            error.style.display = 'block';

          }
          </script>
        <?php
      }
    }

    private function saveDb($allUrls) {
      $query = '';
      $query .= 'UPDATE `'._DB_PREFIX_.'social_bar` SET';
      $query .= '`facebook_url` = \''.$allUrls['SOCIAL_BAR_FACEBOOK_URL'].'\', ';
      $query .= '`youtube_url` =\''.$allUrls['SOCIAL_BAR_YOUTUBE_URL'].'\', ';
      $query .= '`instagram_url` = \''.$allUrls['SOCIAL_BAR_INSTAGRAM_URL'].'\', ';
      $query .= '`line_id` = \''.$allUrls['SOCIAL_BAR_LINE_URL'].'\' ';
      $query .= 'WHERE id_social_bar = 1;';

        if (Db::getInstance()->execute($query)) {
            return true;
        } else {
            return false;
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
      $allData = $this->getDbInfo()[0];
      $this->context->smarty->assign('urls', $allData);
      $this->context->smarty->assign('module_dir', $this->_path);

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
