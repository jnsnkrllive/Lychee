<?php

namespace Lychee\Access;

use Lychee\Modules\Album;
use Lychee\Modules\Module;
use Lychee\Modules\Photo;
use Lychee\Modules\Session;

final class Guest implements Access {

	public function check($fn) {

		switch ($fn) {

			# Album functions
			case 'Album::getAll':		$this->getAlbums(); break;
			case 'Album::get':			$this->getAlbum(); break;
			case 'Album::getPublic':	$this->checkAlbumAccess(); break;

			# Photo functions
			case 'Photo::get':			$this->getPhoto(); break;

			# Session functions
			case 'Session::init':		$this->init(); break;
			case 'Session::login':		$this->login(); break;
			case 'Session::logout':		$this->logout(); break;

			# $_GET functions
			case 'Album::getArchive':	$this->getAlbumArchive(); break;
			case 'Photo::getArchive':	$this->getPhotoArchive(); break;

			# Error
			default:					exit('Error: Function not found! Please check the spelling of the called function.');
										break;

		}

		return true;

	}

	# Album functions

	private function getAlbums() {

		$album = new Album(null);
		echo json_encode($album->getAll(true));

	}

	private function getAlbum() {

		Module::dependencies(isset($_POST['albumID'], $_POST['password']));
		$album = new Album($_POST['albumID']);

		if ($album->getPublic()) {

			# Album public
			if ($album->checkPassword($_POST['password']))	echo json_encode($album->get());
			else											echo 'Warning: Wrong password!';

		} else {

			# Album private
			echo 'Warning: Album private!';

		}

	}

	private function checkAlbumAccess() {

		Module::dependencies(isset($_POST['albumID'], $_POST['password']));
		$album = new Album($_POST['albumID']);

		if ($album->getPublic()) {

			# Album public
			if ($album->checkPassword($_POST['password']))	echo true;
			else											echo false;

		} else {

			# Album private
			echo false;

		}

	}

	# Photo functions

	private function getPhoto() {

		Module::dependencies(isset($_POST['photoID'], $_POST['albumID'], $_POST['password']));
		$photo = new Photo($_POST['photoID']);

		$pgP = $photo->getPublic($_POST['password']);

		if ($pgP===2)		echo json_encode($photo->get($_POST['albumID']));
		else if ($pgP===1)	echo 'Warning: Wrong password!';
		else if ($pgP===0)	echo 'Warning: Photo private!';

	}

	# Session functions

	private function init() {

		global $dbName;

		$session = new Session();
		echo json_encode($session->init(true));

	}

	private function login() {

		Module::dependencies(isset($_POST['user'], $_POST['password']));
		$session = new Session();
		echo $session->login($_POST['user'], $_POST['password']);

	}

	private function logout() {

		$session = new Session();
		echo $session->logout();

	}

	# $_GET functions

	private function getAlbumArchive() {

		Module::dependencies(isset($_GET['albumID'], $_GET['password']));
		$album = new Album($_GET['albumID']);

		if ($album->getPublic()&&$album->getDownloadable()) {

			# Album Public
			if ($album->checkPassword($_GET['password']))	$album->getArchive();
			else											exit('Warning: Wrong password!');

		} else {

			# Album Private
			exit('Warning: Album private or not downloadable!');

		}

	}

	private function getPhotoArchive() {

		Module::dependencies(isset($_GET['photoID'], $_GET['password']));
		$photo = new Photo($_GET['photoID']);

		$pgP = $photo->getPublic($_GET['password']);

		# Photo Download
		if ($pgP===2) {

			# Photo Public
			$photo->getArchive();

		} else {

			# Photo Private
			exit('Warning: Photo private or password incorrect!');

		}

	}

}

?>