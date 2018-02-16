<?php
App::uses('Folder', 'Utility');

class CardFilesController extends AppController {


	var $uses = array('Character', 'Event', 'Player');

	public function isAuthorized($user) {

		if(AuthComponent::user('role_cards')) {
            return true;
	    }

	    return parent::isAuthorized($user);
	}

	public function index() {
	}

	public function listfiles($path = null) {
		if($path == null) {
			$newpath = TMP;
		} else {
			$newpath = TMP . DS . $path;
		}

		$dir = new Folder($newpath);
		$files = $dir->read(true, array('cache','logs','tests','sessions','sample-pcs','pdfs','ledgers'), false);

		$this->set('ajax',json_encode($files));
        $this->layout = 'ajax';
        $this->render('ajax');
	}

	public function createzippdfs() {
		$this->createzips('pdfs');
	}

	public function createzipledgers() {
		$this->createzips('ledgers');
	}

	private function createzips($type = null) {
		$folder = $type . "-" . date("Y");
		$zipfilename = $type . "-" . date("Y-m-d") . ".zip";

		$rootPath = realpath(TMP . DS . $type . DS);

		$zip = new ZipArchive();
		$ret = $zip->open(TMP . DS . $folder . DS . $zipfilename, ZipArchive::CREATE | ZipArchive::OVERWRITE);

		if($ret !== TRUE) {
			throw new NotFoundException('Could not create .zip file');
		} else {
			$options = array('add_path' => $type.'/', 'remove_path' => $rootPath);
			$zip->addPattern('/\.(?:pdf)$/', $rootPath, $options);
    		$zip->close();
		}

		$this->set('ajax',json_encode($rootPath));
        $this->layout = 'ajax';
        $this->render('ajax');
	}

	public function clearfolderspdfs() {
		$this->clearfoldersimpl('pdfs');
	}

	public function clearfoldersledgers() {
		$this->clearfoldersimpl('ledgers');
	}

	private function clearfoldersimpl($type = null) {
		$folder = TMP . DS . $type . DS;
		$files = glob($folder."*.pdf");
		foreach($files as $file) {
			if(is_file($file)) {
				unlink($file);
			}
		}

		$this->set('ajax',json_encode($files));
        $this->layout = 'ajax';
        $this->render('ajax');
	}

	private function endsWith($haystack, $needle) {
	    // search forward starting from end minus needle length characters
	    return $needle === "" || (($temp = strlen($haystack) - strlen($needle)) >= 0 && strpos($haystack, $needle, $temp) !== false);
	}

	public function downloadfile($path = null, $filename = null) {

		if($path == null) {
			throw new NotFoundException('Invalid path');
		} else {
			$newpath = TMP . DS . $path . DS;
		}

		if($filename == null) {
			throw new NotFoundException('Invalid path');
		} else {
			if( !$this->endsWith($filename,".zip") ) {
				throw new NotFoundException('Only supports .zip downloads at this time');
			}

			$filename = substr($filename,0,-4);
		}

		$this->viewClass = 'Media';
		// Download app/outside_webroot_dir/example.zip
		$params = array(
			'id'        => $filename.'.zip',
			'name'      => $filename,
			'download'  => true,
			'extension' => 'zip',
			'path'      => $newpath
		);
		$this->set($params);
	}
}
?>