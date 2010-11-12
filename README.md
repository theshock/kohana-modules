# Kohana 3 modules repository

Here you can see my modules for Kohana 3. 
All modules require PHP 5.3 or higher. They don't work with PHP 5.2!

### Comments
Based on build-in ORM and Auth modules for commenting any model. Just add this in your article view:
	Comments::render($article);
	Comments::form($article);

### LessCSS
Easy [LessCSS](http://lesscss.org/) compiler
	LessCSS::compileAll();

### Markdown
Easy [Markdown](http://daringfireball.net/projects/markdown/) compiler
	$markdown_text = Markdown::parse($text);

### ORM Materialized Path
Easy building trees in MySQL using Materialized Path technique