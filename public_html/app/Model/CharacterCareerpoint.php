<?php
class CharacterCareerpoint extends AppModel {
	public $name = 'CharacterCareerpoint';

	public $hasOne = array('Careerpointtype','CharacterCareerpoint');
}
?>