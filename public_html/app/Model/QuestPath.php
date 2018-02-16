<?php
class QuestPath extends AppModel {
	public $name = 'QuestPath';
	public $hasMany = array('QuestStage' => array('order' => array('QuestStage.quest_stage','QuestStage.stage_code')));
	public $recursive = 2;
}
?>