<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * @package					Publish Bar for EE2
 * @author					Ryan J. Bonnell
 * @copyright				Copyright (c) 2011 Ryan J. Bonnell
 * @link					https://github.com/ryanjbonnell/publish_bar.ee_addon/
 */

class Publish_bar_ext {
	public $settings		= array();
	public $name			= 'Publish Bar';
	public $version			= '1.0';
	public $description		= 'Streamlines publishing by adding a subtle overlay bar at the bottom of the ExpressionEngine entry editing screen.';
	public $settings_exist	= 'n';
	public $docs_url		= 'https://github.com/ryanjbonnell/publish_bar.ee_addon';

	public function __construct($settings = '')	{
		$this->EE =& get_instance();
		$this->settings = $settings;
		$this->classname = get_class($this);
	}

	function activate_extension() {
		$hooks = array(
			'cp_css_end',
			'cp_js_end'
		);

		foreach($hooks as $hook) {
			$this->EE->db->insert('extensions', array(
				'class'		=> $this->classname,
				'method'	=> $hook,
				'hook'		=> $hook,
				'settings'	=> '',
				'priority'	=> 10,
				'version'	=> $this->version,
				'enabled'	=> 'y'
			));
		}
	}

	public function update_extension($current = '') {
		if ($current == '' OR $current == $this->version) {
			return FALSE;
		}

		$this->EE->db->update('extensions', array('version' => $this->version), array('class' => $this->classname));
	}

	public function disable_extension() {
		$this->EE->db->delete('extensions', array('class' => $this->classname));
	}

	public function settings() {
		$settings = array();
		return $settings;
	}

	public function cp_css_end() {
		// Multiple Extensions, Same Hook
		// http://expressionengine.com/user_guide/development/extensions.html#hook

		$css = $this->EE->extensions->last_call;

		// For Debugging Purposes:
		// http://ee.dev/system/index.php?S=0&D=cp&C=css&M=cp_global_ext

		$css .= '
			body {
				margin-bottom: 50px;
			}

			#publish-bar {
				background: #333 none;
				background: rgba(0,0,0, 0.85) none;
				color: #666;
				display: none;
				left: 0;
				padding: 10px 0;
				position: absolute;
				text-align: right;
				bottom: 0;
				width: 100%;
				z-index: 100;
			}

			#publish-bar li {
				display: inline;
				margin-right: 10px;
			}

			#publish-bar #back-to-top {
				margin-left: -6px;
			}

			#publish-bar li a {
				color: #fff;
				padding: 3px 6px;
			}
		';

		return $css;
	}

	public function cp_js_end() {
		$autosave_interval = ($this->EE->config->item('autosave_interval_seconds') === FALSE) ? 300 : $this->EE->config->item('autosave_interval_seconds');

		// Multiple Extensions, Same Hook
		// http://expressionengine.com/user_guide/development/extensions.html#hook

		$javascript = $this->EE->extensions->last_call;

		// For Debugging Purposes:
		// http://ee.dev/system/index.php?S=0&D=cp&C=javascript&M=load&file=ext_scripts

		$javascript .= "
			$('body').prepend('<ins id=\"top\"></ins>');
			$('#publish_submit_buttons').clone().attr('id', 'publish-bar').insertBefore('#tab_menu_tabs');
			$('#publish-bar #submit_button').attr('accesskey', 's');
			$('#publish-bar #autosave_notice').attr('id', 'autosave-notice').removeAttr('style');
			$('#publish-bar').append('<li id=\"back-to-top\"><a href=\"#top\">Back to Top</a></li>');
			$('#publish-bar #back-to-top a').click(function (event) {
				event.preventDefault();
				$('html, body').animate({
					scrollTop: ''
				}, 750);
			});

			$(window).scroll(function () {
				if ($(window).scrollTop() >= 107) {
					$('#publish-bar').css({
						display: 'block',
						position: 'fixed'
					});
				} else {
					$('#publish-bar').css({
						display: 'none'
					});
				}
			});
		";

		if ($autosave_interval > 0) {
			$autosave_interval += 1;

			$javascript .= "
				function refreshAutoSave() {
					var autoSaveText = $('#autosave_notice').text();
					$('#autosave-notice').text(autoSaveText);
				}
				setInterval(refreshAutoSave, $autosave_interval);
			";
		}

		return $javascript;
	}
}
