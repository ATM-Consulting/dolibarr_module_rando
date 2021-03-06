<?php

require 'config.php';
dol_include_once('/rando/class/rando.class.php');
dol_include_once('/rando/class/level.class.php');

if(empty($user->rights->rando->read)) accessforbidden();

$langs->load('abricot@abricot');
$langs->load('rando@rando');


$object = new rando($db);

$hookmanager->initHooks(array('randolist'));

/*
 * Actions
 */

$parameters=array();
$reshook=$hookmanager->executeHooks('doActions',$parameters,$object);    // Note that $action and $object may have been modified by some hooks
if ($reshook < 0) setEventMessages($hookmanager->error, $hookmanager->errors, 'errors');

if (empty($reshook))
{
	// do action from GETPOST ... 
}


/*
 * View
 */

llxHeader('',$langs->trans('randoList'),'','');

//$type = GETPOST('type');
//if (empty($user->rights->rando->all->read)) $type = 'mine';

// TODO ajouter les champs de son objet que l'on souhaite afficher
$sql = 'SELECT t.rowid, t.ref, t.label, t.start, t.stop, t.distance, l.level, t.temps, t.date_creation, t.tms, \'\' AS action

		FROM '.MAIN_DB_PREFIX.'rando t
 		JOIN '.MAIN_DB_PREFIX.'level l ON (t.difficulte = l.rowid)

		WHERE 1=1';

//$sql.= ' AND t.entity IN ('.getEntity('rando', 1).')';
//if ($type == 'mine') $sql.= ' AND t.fk_user = '.$user->id;


$formcore = new TFormCore($_SERVER['PHP_SELF'], 'form_list_rando', 'GET');

$nbLine = !empty($user->conf->MAIN_SIZE_LISTE_LIMIT) ? $user->conf->MAIN_SIZE_LISTE_LIMIT : $conf->global->MAIN_SIZE_LISTE_LIMIT;

$r = new Listview($db, 'rando');
echo $r->render($sql, array(
	'view_type' => 'list' // default = [list], [raw], [chart]
	,'limit'=>array(
		'nbLine' => $nbLine
	)
	,'subQuery' => array()
	,'link' => array()
	,'type' => array(
		'date_creation' => 'date' // [datetime], [hour], [money], [number], [integer]
		,'tms' => 'date'
	)
	,'search' => array(
		'date_creation' => array('search_type' => 'calendars', 'allow_is_null' => true)
		,'tms' => array('search_type' => 'calendars', 'allow_is_null' => false)
		,'ref' => array('search_type' => true, 'table' => 't', 'field' => 'ref')
		,'label' => array('search_type' => true, 'table' => array('t', 't'), 'field' => array('label')) // input text de recherche sur plusieurs champs
		,'start' => array('search_type' => true, 'table' => array('t', 't'), 'field' => array('start'))
		,'stop' => array('search_type' => true, 'table' => array('t', 't'), 'field' => array('stop'))
		,'distance' => array('search_type' => true, 'table' => array('t', 't'), 'field' => array('distance'))
		,'temps' => array('search_type' => true, 'table' => array('t', 't'), 'field' => array('temps'))
		,'level' => array('search_type' => true, 'table' => array('l', 'l'), 'field' => array('level'))
		,'status' => array('search_type' => rando::$TStatus, 'to_translate' => true) // select html, la clé = le status de l'objet, 'to_translate' à true si nécessaire
	)
	,'translate' => array()
	,'hide' => array(
		'rowid'
	)
	,'list' => array(
		'title' => $langs->trans('randoList')
		,'image' => 'title_generic.png'
		,'picto_precedent' => '<'
		,'picto_suivant' => '>'
		,'noheader' => 0
		,'messageNothing' => $langs->trans('Norando')
		,'picto_search' => img_picto('','search.png', '', 0)
	)
	,'title'=>array(
		'ref' => $langs->trans('Ref.')
		,'label' => $langs->trans('Label')
		,'start' => $langs->trans('Start')
		,'stop' => $langs->trans('Stop')
		,'distance' => $langs->trans('Distance')
		,'temps' => $langs->trans('Temps')
		,'level' => $langs->trans('level')
		,'date_creation' => $langs->trans('DateCre')
		,'tms' => $langs->trans('DateMaj')
	)
	,'eval'=>array(
		'ref' => '_getObjectNomUrl(\'@val@\')'
//		,'fk_user' => '_getUserNomUrl(@val@)' // Si on a un fk_user dans notre requête
	)
));

$parameters=array('sql'=>$sql);
$reshook=$hookmanager->executeHooks('printFieldListFooter', $parameters, $object);    // Note that $action and $object may have been modified by hook
print $hookmanager->resPrint;

$formcore->end_form();

llxFooter('');

/**
 * TODO remove if unused
 */
function _getObjectNomUrl($ref)
{
	global $db;

	$o = new rando($db);
	$res = $o->load('', $ref);
	if ($res > 0)
	{
		return $o->getNomUrl(1);
	}

	return '';
}

/**
 * TODO remove if unused
 */
function _getUserNomUrl($fk_user)
{
	global $db;

	$u = new User($db);
	if ($u->fetch($fk_user) > 0)
	{
		return $u->getNomUrl(1);
	}

	return '';
}