<?php
namespace nomination\view;

use nomination\Context;
use nomination\Nominator;
use nomination\Nomination;
use nomination\NominationFactory;
use nomination\UserStatus;

  /**
   * NominatorView
   *
   * See details about a nominator.
   * Supports Ajax
   */
class NominatorView extends \nomination\View
{
    public $nominationId;

    public function getRequestVars(){
        $vars = array('id'   => $this->nominationId,
                      'view' => 'NominatorView');

        return $vars;
    }

    //this is so we can get the id later
    public function setNominationId($id){
      $this->nominationId = $id;
    }

    public function display(Context $context)
    {
        if(!(UserStatus::isCommitteeMember() || UserStatus::isAdmin())){
            throw new \nomination\exception\PermissionException('You are not allowed to see that!');
        }

        $tpl = array();

        $factory = new NominationFactory();
        $nominator = $factory->getNominationbyId($context['id']);

        $tpl['NAME']    = $nominator->getNominatorFullName();
        $tpl['EMAIL']   = $nominator->getNominatorEmailLink();
        $tpl['PHONE']   = $nominator->getNominatorPhone();
        $tpl['ADDRESS'] = $nominator->getNominatorAddress();

        if(isset($context['ajax'])){
            echo \PHPWS_Template::process($tpl, 'nomination', 'admin/nominator.tpl');
            exit();
        } else {
            \Layout::addPageTitle('Nominator View');
            return \PHPWS_Template::process($tpl, 'nomination', 'admin/nominator.tpl');
        }
    }
}
