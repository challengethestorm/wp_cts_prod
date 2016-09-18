<?php

function copyFolder($source, $dest, $exclude = '', $mode = 0771, $override = true) {
	if ($exclude == '') {
		$exclude = array();
	}
	if (is_link($source)) {
		symlink(readlink($source), $dest);
	} elseif (is_dir($source)) {
		// Check if the destination directory already exists, if not create a new one.
		if (!file_exists($dest)) {
			mkdir($dest, $mode, true);
		}

		// Scan all files from the directory and copy them in the destination.
		$files = scandir($source);

		foreach ($files as $file) {
			if (is_link($file)) {
				symlink(readlink($file), $dest);
			} elseif ($file != "." && $file != ".." && $file != ".svn" && $file != "Thumbs.db") {
				$skip = false;
				foreach ($exclude as $exclude_file) {
					if (strpos($file, $exclude_file) !== FALSE) {
						$skip = true;
						break;
					}
				}
				if (!$skip) {
					$fileToCopy = $source . "/" . $file;

					// If it is a directory we need to call owrselvs.
					if (is_dir($fileToCopy)) {
						copyFolder($fileToCopy, $dest . "/" . $file, $exclude, $mode, $override);
					} else {
						if (file_exists($dest . "/" . $file) && !$override) {

						} else {
							copy($fileToCopy, $dest . "/" . $file);
							chmod($dest . "/" . $file, $mode);
						}

					}
				}
			}
		}
	}
}

function deleteDir($dirPath) {
	if (!is_dir($dirPath)) {
		throw new InvalidArgumentException("$dirPath must be a directory");
	}
	if (substr($dirPath, strlen($dirPath) - 1, 1) != '/') {
		$dirPath .= '/';
	}
	$files = glob($dirPath . '*', GLOB_MARK);
	foreach ($files as $file) {
		if (is_dir($file)) {
			deleteDir($file);
		} else {
			unlink($file);
		}
	}
	wp_extend_rrmdir($dirPath);
}

function wp_extend_rrmdir($dir) {
	if (is_dir($dir)) {
		$objects = scandir($dir);
		foreach ($objects as $object) {
			if ($object != "." && $object != "..") {
				if (filetype($dir . "/" . $object) == "dir") {
					rrmdir($dir . "/" . $object);
				} else {
					unlink($dir . "/" . $object);
				}

			}
		}
		reset($objects);
		rmdir($dir);
	}
}