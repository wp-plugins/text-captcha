=== Text Captcha ===
Contributors: troyvit
Tags: comments, captcha, blind
Requires at least: 3.0
Tested up to: 3.1-RC3
Stable tag: 0.9.1

Text Captcha uses riddles and math to make sure your posters are real instead of computers.

== Description ==

It doesn't take much to prove that computers are idiots, but most captchas go too far. Captchas based on graphics don't work with blind people yet they dominate comment forums. This is a logical captcha that uses math and simple riddles to weed out the machines. This plugin comes with 10 predefined questions and the ability to add more. The math part isn't too hard and can be disabled if you're dealing with people who don't like math. This plugin relies on php sessions to store information.

== Installation ==

1. Upload the `text_captcha/` folder to your `/wp-content/plugins/` directory.
1. Make sure wordpress can write to captcha_config.json.
1. Activate the plugin through the 'Plugins' menu in WordPress.
1. Click `Text CAPTCHA Plugin` to configure the plugin (if you like) to toggle which captcha methods you wish to use and modify the questions.

== Frequently Asked Questions ==

= Do I have to change my templates?

Maybe. It uses add_filter on the comments to automagically test the captcha but it requires that your comment submit button be have the default #submit id.

= Does it rely on JavaScript? Sessions?

It uses WP's included jQuery in the admin section and plain old javaScript to position the captcha. It uses sessions to hold the captcha answer.

= What do you have left to do?

* Add some css to make it prettier.
* Test on multiple versions of Wordpress.
* Gather feedback.
* Bulletproof the PHP.
* Bulletproof the JavaScript.
* Add config options for challenge header, error message
* Make the error message more graceful

== Changelog ==

= 0.9 =
* Initial import. 
* multiplication, addition and riddle modules.

= 0.9.1 =
* Hack to make it work in explorer.
