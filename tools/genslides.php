#!/usr/local/bin/php -q
<?php
class presentation_slide {
	var $data;
	var $title;
	var $filename;
}


class presentation_generator {
	var $slides;
	var $presfile;
	var $presfileStart;
	var $directory;
	var $unknown_count = 0;
	var $stderr_fp = 0;

	function presentation_generator ($filename, $directory, $presfile)
	{
		$this->directory = $directory;
		$this->presfile = $presfile;
		print "parsing...";
		$tmp = $this->parseFile($filename);
		$this->presfileStart = $tmp['start'];
		unset($tmp['start']);
		$this->slides = $tmp;
		print "done!\n";
	}

	function warn($msg) {
		if (!$this->stderr_fp) {
			$this->stderr_fp = fopen('php://stderr', 'w');
		}
		fwrite($this->stderr_fp, "\n$msg\n");
	}

	function parseFile ($filename)
	{
		$fp = fopen($filename, "r");
		
		$inslide = 0;
		$beginning = 1;
		$element = 0;
		$buf = "";
		$start = "";
		$data = array();

		while (!feof($fp)) {
			$line = fgets($fp);

			if ($inslide) {
				if (preg_match('@</slide>@', $line)) {
					$data[$element]->data = $buf;
	
					++$element;
					$inslide = 0;
	
					continue;
				}

				$buf .= $line;
			}
				
			if (!$inslide && preg_match('/^<slide.*?>/', $line)) { 
				$beginning = 0;
				$inslide = 1;
				$data[$element] = new presentation_slide;
			    
				preg_match_all('/(title|filename)\=\"([\w\.\s]+)\"/', $line, $matches);
				if (isset($matches[1][0])) {
					$data[$element]->{$matches[1][0]} = $matches[2][0];
				} else {
					$this->warn("No title found.. Assigning 'unknown'");
					$data[$element]->title = "unknown";
				}

				if (isset($matches[1][1])) {
					$data[$element]->{$matches[1][1]} = $matches[2][1];
				}
			}

			if ($beginning) {
				$start .= $line;
			}
		}

		$data['start'] = $start;

		return $data;
	}

	function createFilename($title) {
		if ($title == "unknown") {
			$title .= '_' . $this->unknown_count++;
		}
		
		return str_replace(" ", "_", strtolower($title)). '.xml';
	}

	function xmlEscape($data) {
		return htmlspecialchars($data);
	}

	function generate_slides() {
		$slides = &$this->slides;

		print "generating slides...\n";
		for ($i = 0, $j = count($this->slides); $i < $j; ++$i) {
			$slide = &$this->slides[$i];
			
			if (empty($slide->filename)) {
				$slide->filename = $this->createFilename($slide->title);
			}
			$slide->filename = "{$this->directory}/{$slide->filename}";
			print "Generating ({$slide->title}): {$slide->filename}...";

			$fp = fopen($slide->filename, "w");
			fwrite($fp, '<slide title="' . $this->xmlEscape($slide->title) . '">');
			fwrite($fp, $slide->data);
			fwrite($fp, '</slide>');
			fclose($fp);

			print " done!\n";
		}
		print "slides generated!\n";
	}

	function generate_presentation() {
		print "generating presentation file: {$this->presfile}...";
		$fp = fopen($this->presfile, "w");
		fwrite($fp, $this->presfileStart);
		foreach ($this->slides as $slide) {
			fwrite($fp, "<slide>{$slide->filename}</slide>\n");
		}
		fwrite($fp, "</presentation>");
		print " done!\n";
	}
		
}

$p = new presentation_generator($_SERVER['argv'][1], $_SERVER['argv'][2], $_SERVER['argv'][3]);
$p->generate_slides();
$p->generate_presentation();
?>
