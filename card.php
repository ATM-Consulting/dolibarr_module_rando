<?php

require 'config.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/functions.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/html.form.class.php';

dol_include_once('/rando/class/rando.class.php');
dol_include_once('/rando/lib/rando.lib.php');

if(empty($user->rights->rando->read)) accessforbidden();

$langs->load('rando@rando');

$action = GETPOST('action');
$id = GETPOST('id', 'int');
$ref = GETPOST('ref');

$mode = 'view';
if (empty($user->rights->rando->write)) $mode = 'view'; // Force 'view' mode if can't edit object
else if ($action == 'create' || $action == 'edit') $mode = 'edit';

$object = new rando($db);

if (!empty($id)) $object->load($id, '');
elseif (!empty($ref)) $object->loadBy($ref, 'ref');

$hookmanager->initHooks(array('randocard', 'globalcard'));

//var_dump($_POST); exit;
/*
 * Actions
 */

$parameters = array('id' => $id, 'ref' => $ref, 'mode' => $mode);
$reshook = $hookmanager->executeHooks('doActions', $parameters, $object, $action); // Note that $action and $object may have been modified by some
if ($reshook < 0) setEventMessages($hookmanager->error, $hookmanager->errors, 'errors');

// Si vide alors le comportement n'est pas remplacé
if (empty($reshook))
{
	$error = 0;
	switch ($action) {
		case 'save':
			$object->setValues($_REQUEST); // Set standard attributes
			
	
			
//			$object->date_other = dol_mktime(GETPOST('starthour'), GETPOST('startmin'), 0, GETPOST('startmonth'), GETPOST('startday'), GETPOST('startyear'));

			// Check parameters
//			if (empty($object->date_other))
//			{
//				$error++;
//				setEventMessages($langs->trans('warning_date_must_be_fill'), array(), 'warnings');
//			}
			
			// ... 
			
			if ($error > 0)
			{
				$mode = 'edit';
				break;
			}
			
			$object->save(empty($object->ref));
			
			header('Location: '.dol_buildpath('/rando/card.php', 1).'?id='.$object->id);
			exit;
			
			break;
		case 'confirm_clone':
			$object->cloneObject();
			
			header('Location: '.dol_buildpath('/rando/card.php', 1).'?id='.$object->id);
			exit;
			break;
		case 'modif':
			if (!empty($user->rights->rando->write)) $object->setDraft();
				
			break;
		case 'confirm_validate':
			if (!empty($user->rights->rando->write)) $object->setValid();
			
			header('Location: '.dol_buildpath('/rando/card.php', 1).'?id='.$object->id);
			exit;
			break;
		case 'confirm_delete':
			if (!empty($user->rights->rando->write)) $object->delete($user);
			
			header('Location: '.dol_buildpath('/rando/list.php', 1));
			exit;
			break;
		// link from llx_element_element
		case 'dellink':
			$object->generic->deleteObjectLinked(null, '', null, '', GETPOST('dellinkid'));
			header('Location: '.dol_buildpath('/rando/card.php', 1).'?id='.$object->id);
			exit;
			break;
	}
}


/**
 * View
 */

$title=$langs->trans("rando");
$arrayofcss=array('/rando/css/style_rando.css');
llxHeader('',$title, '', '', 0, 0,'', $arrayofcss);
if ($action == 'create' && $mode == 'edit')
{
	load_fiche_titre($langs->trans("Newrando"));
	dol_fiche_head();
}
else
{
	$head = rando_prepare_head($object);
	$picto = 'generic';
	dol_fiche_head($head, 'card', $langs->trans("rando"), 0, $picto);
}

$formcore = new TFormCore;
$formcore->Set_typeaff($mode);

$form = new Form($db);

//$myarray = array('facile'=>'Facile','moyen'=>'Moyen','difficile'=>'Difficile');
//var_dump($myarray);
//exit;

$sql = 'SELECT t.level, t.rowid';
$sql.= ' FROM '.MAIN_DB_PREFIX.'level t ';
//$sql.= ' WHERE 1=1';
$dataresult = $db->query($sql);

$TlistSelectDifficulte = array();

while ($display = $db->fetch_object($dataresult)) {
	$TlistSelectDifficulte[$display->rowid] =  $display->level;
}

var_dump($TlistSelectDifficulte);

//var_dump($myarray);

$formconfirm = getFormConfirmrando($PDOdb, $form, $object, $action);
if (!empty($formconfirm)) echo $formconfirm;

$TBS=new TTemplateTBS();
$TBS->TBS->protect=false;
$TBS->TBS->noerr=true;

if ($mode == 'edit') echo $formcore->begin_form($_SERVER['PHP_SELF'], 'form_rando');
//var_dump($myarray);

$linkback = '<a href="'.dol_buildpath('/rando/list.php', 1).'">' . $langs->trans("BackToList") . '</a>';
print $TBS->render('tpl/card.tpl.php'
	,array() // Block
	,array(
		'object'=>$object
		,'view' => array(
			'mode' => $mode
			,'action' => 'save'
			,'urlcard' => dol_buildpath('/rando/card.php', 1)
			,'urllist' => dol_buildpath('/rando/list.php', 1)
			,'showRef' => ($action == 'create') ? $langs->trans('Draft') : $form->showrefnav($object, 'ref', $linkback, 1, 'ref', 'ref', '')
			,'showLabel' => $formcore->texte('', 'label', $object->label, 80, 255)
			,'showStart' => $formcore->texte('', 'start', $object->start, 80, 255)
			,'showStop' => $formcore->texte('', 'stop', $object->stop, 80, 255)
			,'showDistance' => $formcore->texte('', 'distance', $object->distance, 80, 255)
			,'showTemps' => $formcore->texte('', 'temps', $object->temps, 80, 255)
			,'showDifficulte' => $form->selectarray('difficulte',$TlistSelectDifficulte,$object->difficulte)
			,'showNote' => $formcore->zonetexte('', 'note', $object->note, 80, 8)
			,'showStatus' => $object->getLibStatut(1)
		)
		,'langs' => $langs
		,'user' => $user
		,'conf' => $conf
		,'rando' => array(
			'STATUS_DRAFT' => rando::STATUS_DRAFT
			,'STATUS_VALIDATED' => rando::STATUS_VALIDATED
			,'STATUS_REFUSED' => rando::STATUS_REFUSED
			,'STATUS_ACCEPTED' => rando::STATUS_ACCEPTED
		)
	)
);

if ($mode == 'edit') echo $formcore->end_form();

if ($mode == 'view' && $object->id) $somethingshown = $form->showLinkedObjectBlock($object);

llxFooter();