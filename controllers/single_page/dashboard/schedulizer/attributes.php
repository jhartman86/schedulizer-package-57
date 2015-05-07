<?php namespace Concrete\Package\Schedulizer\Controller\SinglePage\Dashboard\Schedulizer {

    use \Concrete\Package\Schedulizer\Controller\DashboardController;
    use \Concrete\Core\Attribute\Type AS AttributeType;
    use \Concrete\Core\Attribute\Key\Category AS AttributeKeyCategory;
    use \Concrete\Package\Schedulizer\Src\Attribute\Key\SchedulizerEventKey;
    use Package;
    use \Exception;
    use Loader;

    class Attributes extends DashboardController {

        public $helpers = array('form');

        public function on_start(){
            parent::on_start();
            $this->set('category', AttributeKeyCategory::getByHandle(SchedulizerEventKey::ATTR_CATEGORY_HANDLE));
            $otypes = AttributeType::getList(SchedulizerEventKey::ATTR_CATEGORY_HANDLE);
            $types = array();
            foreach($otypes AS $at){ /** @var $at AttributeType */
                $types[$at->getAttributeTypeID()] = $at->getAttributeTypeDisplayName();
            }
            $this->set('types', $types);
        }

        public function view(){
            $this->set('attribs', SchedulizerEventKey::getList());
        }

        public function delete( $akID, $token = null ){
            try {
                $akObj = SchedulizerEventKey::getByID($akID);
                if( !($akObj instanceof SchedulizerEventKey) ){
                    throw new Exception(t('Invalid attribute ID.'));
                }
                $valt = Loader::helper('validation/token');
                if( !($valt->validate('delete_attribute', $token)) ){
                    throw new Exception($valt->getErrorMessage());
                }
                $akObj->delete();
                $this->redirect('/dashboard/schedulizer/attributes', 'attribute_deleted');
            }catch(Exception $e){
                $this->set('error', $e);
            }
        }

        public function select_type(){
            $atID = $this->request('atID');
            $at   = AttributeType::getByID($atID);
            $this->set('type', $at);
        }

        public function add(){
            $this->select_type();
            $type = $this->get('type');
            $typeController = $type->getController();
            $this->error = $typeController->validateKey($this->post());
            if( !$this->error->has() ){
                $type = AttributeType::getByID($this->post('atID'));
                SchedulizerEventKey::add($type, $this->post(), Package::getByHandle('schedulizer'));
                $this->redirect('/dashboard/schedulizer/attributes/', 'attribute_created');
            }
        }

        public function attribute_deleted() {
            $this->set('message', t('Attribute Deleted.'));
            $this->view();
        }

        public function attribute_created() {
            $this->set('message', t('Attribute Created.'));
            $this->view();
        }

        public function attribute_updated() {
            $this->set('message', t('Attribute Updated.'));
            $this->view();
        }

        public function edit( $akID = 0 ){
            if( $this->post('akID') ){
                $akID = $this->post('akID');
            }
            $key = SchedulizerEventKey::getByID($akID);
            if( !is_object($key) || $key->isAttributeKeyInternal() ){
                $this->redirect('/dashboard/schedulizer/attributes');
            }
            $type = $key->getAttributeType();
            $this->set('key', $key);
            $this->set('type', $type);
            if( $this->isPost() ){
                $typeController = $type->getController();
                $typeController->setAttributeKey($key);
                $error = $typeController->validateKey($this->post());
                if( $error->has() ){
                    $this->set('error', $error);
                    return;
                }
                $key->update($this->post());
                $this->redirect('/dashboard/schedulizer/attributes', 'attribute_updated');
            }
        }

    }

}