<?php
namespace Carrot3;

class PictogramConfigCompiler extends DefaultConfigCompiler {

	protected function getContents (?iterable $config) {
		$pictograms = [];
		foreach (Tuple::create($config) as $entry) {
			foreach ($entry['names'] as $name) {
				$pictograms['codes'][$name] = $entry['pictograms'];
				$pictograms['names'][$entry['pictograms']['Docomo']][] = $name;
			}
		}
		return $pictograms;
	}
}
