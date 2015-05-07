<?php namespace Schedulizer\Tests\Package {

    use Request;
    use UserInfo;
    use Permissions;
    use \Concrete\Package\Schedulizer\Src\Permission\Key\SchedulizerKey;

    class AddTagTest extends \PHPUnit_Framework_TestCase {

        public function testOne(){
            /** @var $ui \Concrete\Core\User\UserInfo */
            $uiInstance = UserInfo::getByID(3);
            // Simulate this request as a specific user
            $req = Request::getInstance();
            $req->setCustomRequestUser($uiInstance);

            $p = new Permissions();
            if( $p->canCreateTag() ){
                echo 'tagger';
            }
            return;

            // Test the permission key
            $tagPermKey = SchedulizerKey::getByHandle('create_tag');
            $tagPermKey->canCreateTag();
            if( $tagPermKey->can() ){
            //if( SchedulizerKey::canCreateTag() ){
                echo 'yup!';
            }
        }

    }

}