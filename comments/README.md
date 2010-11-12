# Universal comments module
This is universal comments module for Kohana 3, which based on build in auth & orm.
`$model` can be any instance, that can be commented, e.g. article or product

## MySQL dump:

	CREATE TABLE `comments` (
	  `id` int(11) NOT NULL AUTO_INCREMENT,
	  `model_id` int(11) NOT NULL,
	  `model_name` varchar(128) NOT NULL,
	  `username` varchar(32) DEFAULT NULL,
	  `author_id` int(11) DEFAULT NULL,
	  `content` text NOT NULL,
	  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
	  `modified` timestamp NULL DEFAULT NULL,
	  PRIMARY KEY (`id`),
	  KEY `model_id` (`model_id`),
	  KEY `user_id` (`author_id`)
	) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

## Render comments in view

	Comments::render($model);

## Render add comment form in view

	Comments::form($model);

## Render edit comment form in view

	Comments::form($model, $id);

## Get all comments for model

	$comments = Comments::get($model);
	// you can build query with result:
	$comments->where('author_id', '=', $author_id)->find_all();

------------------------

# Some tricks

## Admin can post with another name:

	class Comments extends Kohana_Comments {
		protected function can_set_username() {
			return !$this->auth->logged_in() || $this->auth->get_user()->is_admin();
		}
	}
