<?php

function splitRules ( $rules_str ) {

	$parts = preg_split ( '/;/', $rules_str );
	
	while (list ($key, $val) = each($parts)) {

		$ruleparts = preg_split ( '/:/', $val );
		
		$rules["$ruleparts[0]"] = $ruleparts[1];
	}
	
	return $rules;
}

?>
