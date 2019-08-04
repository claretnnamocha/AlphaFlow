<?php

$language = 'python';

$code = '
if True:
	print(\'working at last\');
	print(\'working at last\');
	print(\'working at last\');

';

#	Supported Languages and their extensions
$extensions = [
	'node' => 'js',
	'php' => 'php',
	'python' => 'py'
];

# Check if language is supported
$ext = $extensions[$language] ?? null;
if ($ext == null) {
	throw new Exception("Error Language is unknown or currently unsupported");
}

#Setting up temp file
$filename = 'alpha.'.$ext;
system('touch '.$filename);

$code = preg_replace('#(\t)#', '--t--', trim($code));
$code = preg_replace('#(\s{2,})#', '--nl--', trim($code));
$code = preg_replace('#(--t--)#', '	', trim($code));

$diff_lines = explode('--nl--', $code);

# if it is php code
$started = false;
if ($ext =='php') {
	$started = true;
	system('echo ^<?php > '.$filename);
}

foreach ($diff_lines as $key => $value) {
	if ($key == 0) {		
		system('echo '.$value . '>> '.$filename);
		continue;
	}
	system('echo '.$value. '>> '.$filename);
}
system('cls');
$lang_exe = __DIR__.'/../AFN/bin/'.$language.'.exe';
print($lang_exe);
// print(json_encode(file_exists($lang_exe)));
$result = shell_exec($lang_exe.' '.$filename);
system('del '.$filename);

// print(trim($result));