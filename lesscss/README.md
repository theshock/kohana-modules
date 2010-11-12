# Less CSS for Kohana 3

Less CSS : http://lesscss.org/
Based on : http://leafo.net/lessphp/

## Using:

Put `LessCSS::compileAll();` in your script as soon as possible

## Config example:

	return array (
		'files' => array (
			APPPATH . 'files/admin.less'  => PUBLICPATH . 'files/admin.css',
			APPPATH . 'files/styles.less' => PUBLICPATH . 'files/styles.css'
		),
		'force_update' => true
	);

#### Your CSS files must be writeable (chmod = 0777) !!!

`force_update = true` means that css files will update every request (not only if less changed)
it can be used, when you are developing LessCss-framework in file, which is not setted in config


