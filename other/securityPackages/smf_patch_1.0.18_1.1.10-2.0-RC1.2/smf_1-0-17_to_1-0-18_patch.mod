<edit file>
$boarddir/index.php
</edit file>
<search for>
* Software Version:           SMF 1.0.17                                          *
</search for>

<replace>
* Software Version:           SMF 1.0.18                                          *
</replace>


<search for>
$forum_version = 'SMF 1.0.17';
</search for>

<replace>
$forum_version = 'SMF 1.0.18';
</replace>



<edit file>
$sourcedir/ManageMembers.php
</edit file>
<search for>
* Software Version:           SMF 1.0.13                                          *
</search for>

<replace>
* Software Version:           SMF 1.0.18                                          *
</replace>

<search for>
				'url' => 'http://www.apnic.net/apnic-bin/whois2.pl?searchtext=' . $searchip
</search for>

<replace>
				'url' => 'http://wq.apnic.net/apnic-bin/whois.pl?searchtext=' . $searchip
</replace>


<search for>
				'url' => 'http://ws.arin.net/cgi-bin/whois.pl?queryinput=' . $searchip
</search for>

<replace>
				'url' => 'http://ws.arin.net/whois/?queryinput=' . $searchip
</replace>


<search for>
				'url' => 'http://www.ripe.net/perl/whois?searchtext=' . $searchip
</search for>

<replace>
				'url' => 'http://www.db.ripe.net/whois?searchtext=' . $searchip
</replace>



<edit file>
$sourcedir/Subs-Auth.php
</edit file>
<search for>
* Software Version:           SMF 1.0.14                                          *
</search for>

<replace>
* Software Version:           SMF 1.0.18                                          *
</replace>


<search for>
		// Version 4.3.2 didn't store the cookie of the new session.
		if (version_compare(PHP_VERSION, '4.3.2') === 0)
			setcookie(session_name(), session_id(), time() + $cookie_length, $cookie_url[1], '', 0);
</search for>

<replace>
		// Version 4.3.2 didn't store the cookie of the new session.
		if (version_compare(PHP_VERSION, '4.3.2') === 0 || (isset($_COOKIE[session_name()]) && $_COOKIE[session_name()] != session_id()))
			setcookie(session_name(), session_id(), time() + $cookie_length, $cookie_url[1], '', 0);
</replace>



<edit file>
$sourcedir/Packages.php
</edit file>
<search for>
* Software Version:           SMF 1.0.15                                          *
</search for>

<replace>
* Software Version:           SMF 1.1.10                                          *
</replace>


<search for>
						'action' => strtr($mod_action['filename'], array($boarddir => '.')),
						'description' => $failed ? $txt['package_action_failure'] : $txt['package_action_success']
</search for>

<replace>
						'action' => htmlspecialchars(strtr($mod_action['filename'], array($boarddir => '.'))),
						'description' => $failed ? $txt['package_action_failure'] : $txt['package_action_success']
</replace>


<search for>
						'action' => strtr($mod_action['filename'], array($boarddir => '.')),
						'description' => $txt['package_action_missing']
</search for>

<replace>
						'action' => htmlspecialchars(strtr($mod_action['filename'], array($boarddir => '.'))),
						'description' => $txt['package_action_missing']
</replace>


<search for>
						'action' => strtr($mod_action['filename'], array($boarddir => '.')),
						'description' => $txt['package_action_error']
</search for>

<replace>
						'action' => htmlspecialchars(strtr($mod_action['filename'], array($boarddir => '.'))),
						'description' => $txt['package_action_error']
</replace>


<search for>
				'type' => $txt['package57'],
				'action' => $action['filename']
</search for>

<replace>
				'type' => $txt['package57'],
				'action' => htmlspecialchars($action['filename'])
</replace>


<search for>
				'type' => $txt['package50'] . ' ' . ($action['type'] == 'create-dir' ? $txt['package55'] : $txt['package54']),
				'action' => strtr($action['destination'], array($boarddir => '.'))
</search for>

<replace>
				'type' => $txt['package50'] . ' ' . ($action['type'] == 'create-dir' ? $txt['package55'] : $txt['package54']),
				'action' => htmlspecialchars(strtr($action['destination'], array($boarddir => '.')))
</replace>


<search for>
				'type' => $txt['package53'] . ' ' . ($action['type'] == 'require-dir' ? $txt['package55'] : $txt['package54']),
				'action' => strtr($action['destination'], array($boarddir => '.'))
</search for>

<replace>
				'type' => $txt['package53'] . ' ' . ($action['type'] == 'require-dir' ? $txt['package55'] : $txt['package54']),
				'action' => htmlspecialchars(strtr($action['destination'], array($boarddir => '.')))
</replace>


<search for>
				'type' => $txt['package51'] . ' ' . ($action['type'] == 'move-dir' ? $txt['package55'] : $txt['package54']),
				'action' => strtr($action['source'], array($boarddir => '.')) . ' => ' . strtr($action['destination'], array($boarddir => '.'))
</search for>

<replace>
				'type' => $txt['package51'] . ' ' . ($action['type'] == 'move-dir' ? $txt['package55'] : $txt['package54']),
				'action' => htmlspecialchars(strtr($action['source'], array($boarddir => '.'))) . ' => ' . htmlspecialchars(strtr($action['destination'], array($boarddir => '.')))
</replace>


<search for>
				'type' => $txt['package52'] . ' ' . ($action['type'] == 'remove-dir' ? $txt['package55'] : $txt['package54']),
				'action' => strtr($action['filename'], array($boarddir => '.'))
</search for>

<replace>
				'type' => $txt['package52'] . ' ' . ($action['type'] == 'remove-dir' ? $txt['package55'] : $txt['package54']),
				'action' => htmlspecialchars(strtr($action['filename'], array($boarddir => '.')))
</replace>



<edit file>
$sourcedir/Register.php
</edit file>

<search for>
* Software Version:           SMF 1.0.17                                          *
</search for>

<replace>
* Software Version:           SMF 1.0.18                                          *
</replace>


<search for>
		elseif (isset($_POST['emailPassword']))
</search for>

<replace>
		elseif (isset($_POST['emailPassword']) || empty($_POST['password']))
</replace>



<edit file>
$themedir/Register.template.php
</edit file>

<search for>
			if (document.creator.emailActivate.checked)
</search for>

<replace>
			if (document.creator.emailActivate.checked || document.creator.password.value == \'\')
</replace>


<search for>
					<input type="password" name="password" size="30" /><br />
</search for>

<replace>
					<input type="password" name="password" size="30" onchange="onCheckChange();" /><br />
</replace>


<search for>
					<input type="checkbox" name="emailPassword" checked="checked"', !empty($modSettings['registration_method']) && $modSettings['registration_method'] == 1 ? ' disabled="disabled"' : '', ' class="check" /><br />
</search for>

<replace>
					<input type="checkbox" name="emailPassword" checked="checked" disabled="disabled" class="check" /><br />
</replace>
