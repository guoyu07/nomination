<?php

  /**
   * NomineeView
   *
   * View all details for nominee and their nominations.
   * All nominations related to this nominee for current term are shown.
   * An administrator can set a nomination's winning status in this view.
   */

PHPWS_Core::initModClass('nomination', 'View.php');
PHPWS_Core::initModClass('nomination', 'Context.php');
PHPWS_Core::initModClass('nomination', 'Nominee.php');
PHPWS_Core::initModClass('nomination', 'Nomination.php');

class NomineeView extends OmNomView {
    public $nominationId;

    public function display(Context $context)
    {
        if(!(UserStatus::isCommitteeMember() || UserStatus::isAdmin())){
            throw new PermissionException('You are not allowed to see that!');
        }
        
        $tpl = array();

	PHPWS_Core::initModClass('nomination', 'NominationFactory.php');

	$factory = new NominationFactory;
	$nominee = $factory::getNominationById($context['id']);

        $tpl['NAME']        = $nominee->getFullName();
        $tpl['MAJOR']       = $nominee->getDeptMajor();
        $tpl['YEARS']       = $nominee->getYearsAtASU();
        $tpl['EMAIL']       = $nominee->getEmailLink();

	/*        $db = new PHPWS_DB('nomination_nomination');
        $db->addWhere('id', $nominee->id);
        $db->addOrder('winner desc');
	//	test($db->select(),1);
	$results = $db->getObjects('DBNomination');
	
        
        if(PHPWS_Error::logIfError($results)){
            PHPWS_Core::initModClass('nomination', 'exception/DatabaseException.php');
            throw new DatabaseException('Database asploded');
        } 
        if(is_null($results) || empty($results)){
            PHPWS_Core::initModClass('nomination', 'exception/DatabaseException.php');
            throw new DatabaseException('Invalid Nominee ID');
        }
	*/
        $num = 0;
        $jsVars = array();
        $nomIsWinner = False;
	//        foreach($results as $nomination){
	//	foreach($nominee as $nomination){
            $num++;
	    //            $context['id'] = $nomination->getId();
	    $context['id'] = $nominee->getId();
            PHPWS_Core::initModClass('nomination', 'view/NominationView.php');
            $nominationView = new NominationView();

            $nomination_is_winner = $nominee->isWinner();
            if($nomination_is_winner)$nomIsWinner = True;
            
            if(UserStatus::isAdmin()){
                $icon = $nominee->isWinner() ? 'mod/nomination/img/tango/actions/list-remove-red.png':
                    'mod/nomination/img/tango/actions/list-add-green.png';
                $award_icon = 'mod/nomination/img/tango/mimetypes/application-certificate.png';
            } else {
                // Don't show if nomination is winner to committee members
                $icon = 'images/icons/blank.png';
                $award_icon = 'images/icons/blank.png';
            }
            $tpl['nominations'][] = array('CONTENT' => $nominationView->display($context),
                                          'NUM' => $num,
                                          'ICON' => PHPWS_SOURCE_HTTP.$icon,
                                          'AWARD_ICON' => PHPWS_SOURCE_HTTP.$award_icon,
                                          'DOWN_PNG_HACK' => PHPWS_SOURCE_HTTP."mod/nomination/img/arrow_down.png");

	    
            // pass this to javascript
            $jsVars['collapse'][] = array('NUM' => $num, 'ID' => $nominee->getId());
            $jsVars['winner'][] = array('NUM' => $num, 'ID' => $nominee->getId(), 'WINNER' => $nominee->isWinner());
	    // }


        javascript('jquery');
        // JS Collapse; Admin and Committee
        javascriptMod('nomination', 'nomCollapse', 
                      array('noms' => json_encode($jsVars['collapse']),
                            'PHPWS_SOURCE_HTTP' => PHPWS_SOURCE_HTTP));
        // Full path is needed for images
        $tpl['PHPWS_SOURCE_HTTP'] = PHPWS_SOURCE_HTTP;

        Layout::addPageTitle('Nominee View');

        if(UserStatus::isAdmin()){
            // JS set winner; Admin only
            javascriptMod('nomination', 'nomWinner', array('noms' => json_encode($jsVars['winner']),
                                                    'PHPWS_SOURCE_HTTP' => PHPWS_SOURCE_HTTP));
            // If nomination is winner then set the winner flag beside the
            // nominee's name in big letters
            if($nomIsWinner) $tpl['WINNER'] = '(Winner)';
            
            return PHPWS_Template::process($tpl, 'nomination', 'admin/nominee.tpl');
        }
        return PHPWS_Template::process($tpl, 'nomination', 'committee/nominee.tpl');
    }

    public function getRequestVars(){
        $vars = array('id'   => $this->nominationId,
                      'view' => 'NomineeView');

        return $vars;
    }
    
    public function setNominationId($id){
      $this->nominationId = $id;
    }
    
}
?>
