<?php
/**
 * Simple Machines Forum (SMF)
 *
 * @package SMF
 * Tema Echo Echo con boostrap v3.3.6 para SMF 2.0.11, en este entonces la más estable
 * Autor: Dhayzon 
 * Contacto: Dhayzon.com
 * Code licensed MIT, docs CC BY 3.0.
 * @author Simple Machines
 * @copyright 2011 Simple Machines
 * @license http://www.simplemachines.org/about/smf/license.php BSD
 *
 * @version 2.0
 */

function template_main()
{
	global $context, $settings, $options, $txt, $scripturl, $modSettings;

	// Show some statistics if stat info is off.
	if (!$settings['show_stats_index'])
		echo '
	<div id="index_common_stats">
		', $txt['members'], ': ', $context['common_stats']['total_members'], ' &nbsp;&#8226;&nbsp; ', $txt['posts_made'], ': ', $context['common_stats']['total_posts'], ' &nbsp;&#8226;&nbsp; ', $txt['topics'], ': ', $context['common_stats']['total_topics'], '
		', ($settings['show_latest_member'] ? ' ' . $txt['welcome_member'] . ' <strong>' . $context['common_stats']['latest_member']['link'] . '</strong>' . $txt['newest_member'] : '') , '
	</div>';

	// Show the news fader?  (assuming there are things to show...)
	if ($settings['show_newsfader'] && !empty($context['fader_news_lines']))
	{
		echo '
	<div id="newsfader" class="alert alert-warning"> 
		<div class="cat_bar">
			<h3>
				<img id="newsupshrink" src="', $settings['images_url'], '/collapse.gif" alt="*" title="', $txt['upshrink_description'], '"  style="display: none;"  class="pull-right"/>
				', $txt['news'], '
			</h3>
		</div>
		<ul class="list-inline" id="smfFadeScroller"', empty($options['collapse_news_fader']) ? '' : ' style="display: none;"', '>';

			foreach ($context['news_lines'] as $news)
				echo '
			<li>', $news, '</li>';

	echo '
		</ul> 
	</div> 
	<script type="text/javascript" src="', $settings['default_theme_url'], '/scripts/fader.js"></script>
	<script type="text/javascript"><!-- // --><![CDATA[

		// Create a news fader object.
		var oNewsFader = new smf_NewsFader({
			sSelf: \'oNewsFader\',
			sFaderControlId: \'smfFadeScroller\',
			sItemTemplate: ', JavaScriptEscape('<strong>%1$s</strong>'), ',
			iFadeDelay: ', empty($settings['newsfader_time']) ? 5000 : $settings['newsfader_time'], '
		});

		// Create the news fader toggle.
		var smfNewsFadeToggle = new smc_Toggle({
			bToggleEnabled: true,
			bCurrentlyCollapsed: ', empty($options['collapse_news_fader']) ? 'false' : 'true', ',
			aSwappableContainers: [
				\'smfFadeScroller\'
			],
			aSwapImages: [
				{
					sId: \'newsupshrink\',
					srcExpanded: smf_images_url + \'/collapse.gif\',
					altExpanded: ', JavaScriptEscape($txt['upshrink_description']), ',
					srcCollapsed: smf_images_url + \'/expand.gif\',
					altCollapsed: ', JavaScriptEscape($txt['upshrink_description']), '
				}
			],
			oThemeOptions: {
				bUseThemeSettings: ', $context['user']['is_guest'] ? 'false' : 'true', ',
				sOptionName: \'collapse_news_fader\',
				sSessionVar: ', JavaScriptEscape($context['session_var']), ',
				sSessionId: ', JavaScriptEscape($context['session_id']), '
			},
			oCookieOptions: {
				bUseCookie: ', $context['user']['is_guest'] ? 'true' : 'false', ',
				sCookieName: \'newsupshrink\'
			}
		});
	// ]]></script>';
	}

	echo '
	<div id="boardindex_table" class="container-fluid">
		<div class="row">';

	/* Each category in categories is made up of:
	id, href, link, name, is_collapsed (is it collapsed?), can_collapse (is it okay if it is?),
	new (is it new?), collapse_href (href to collapse/expand), collapse_image (up/down image),
	and boards. (see below.) */
	foreach ($context['categories'] as $category)
	{
		// If theres no parent boards we can see, avoid showing an empty category (unless its collapsed)
		if (empty($category['boards']) && !$category['is_collapsed'])
			continue;

		echo '
			<div class="panel panel-default" id="category_', $category['id'], '">
				 <div class="panel-heading">
							<h3>';

		// If this category even can collapse, show a link to collapse it.
		if ($category['can_collapse'])
			echo '
								<a class="collapse pull-right" href="', $category['collapse_href'], '" style="display:block">', $category['collapse_image'], '</a>';

		if (!$context['user']['is_guest'] && !empty($category['show_unread']))
			echo '
								<a class="unreadlink pull-right" href="', $scripturl, '?action=unread;c=', $category['id'], '">', $txt['view_unread_category'], '</a>';

		echo '
								', $category['link'], '
							</h3>
				</div> 
			  ';

		// Assuming the category hasn't been collapsed...
		if (!$category['is_collapsed'])
		{

		echo '
			<ul class="list-group" id="category_', $category['id'], '_boards">';
			/* Each board in each category's boards has:
			new (is it new?), id, name, description, moderators (see below), link_moderators (just a list.),
			children (see below.), link_children (easier to use.), children_new (are they new?),
			topics (# of), posts (# of), link, href, and last_post. (see below.) */
			foreach ($category['boards'] as $board)
			{
				echo '
				<li id="board_', $board['id'], '" class="list-group-item">
					<div class="uIcon ', !empty($board['children']) ? 'children' : '', '">
						<a href="', ($board['is_redirect'] || $context['user']['is_guest'] ? $board['href'] : $scripturl . '?action=unread;board=' . $board['id'] . '.0;children'), '">';

				// If the board or children is new, show an indicator.
				if ($board['new'] || $board['children_new'])
					echo '
							<img src="', $settings['images_url'], '/', $context['theme_variant_url'], 'on', $board['new'] ? '' : '2', '.png" alt="', $txt['new_posts'], '" title="', $txt['new_posts'], '" />';
				// Is it a redirection board?
				elseif ($board['is_redirect'])
					echo '
							<img src="', $settings['images_url'], '/', $context['theme_variant_url'], 'redirect.png" alt="*" title="*" />';
				// No new posts at all! The agony!!
				else
					echo '
							<img src="', $settings['images_url'], '/', $context['theme_variant_url'], 'off.png" alt="', $txt['old_posts'], '" title="', $txt['old_posts'], '" />';

				echo '
						</a>
					</div><div class="uInfo">
						<a class="subject" href="', $board['href'], '" id="b', $board['id'], '">', $board['name'], '</a>';

				// Has it outstanding posts for approval?
				if ($board['can_approve_posts'] && ($board['unapproved_posts'] || $board['unapproved_topics']))
					echo '
						<a href="', $scripturl, '?action=moderate;area=postmod;sa=', ($board['unapproved_topics'] > 0 ? 'topics' : 'posts'), ';brd=', $board['id'], ';', $context['session_var'], '=', $context['session_id'], '" title="', sprintf($txt['unapproved_posts'], $board['unapproved_topics'], $board['unapproved_posts']), '" class="moderation_link">(!)</a>';

				echo '

						<p>', $board['description'] , '</p>';

				// Show the "Moderators: ". Each has name, href, link, and id. (but we're gonna use link_moderators.)
				if (!empty($board['moderators']))
					echo '
						<p class="moderators">', count($board['moderators']) == 1 ? $txt['moderator'] : $txt['moderators'], ': ', implode(', ', $board['link_moderators']), '</p>';

				// Show some basic information about the number of posts, etc.
					echo '
					</div><div class="uStats ">
							<p><span class="glyphicon ', $board['is_redirect'] ? 'glyphicon-send':'glyphicon-comment' ,'" aria-hidden="true"></span> ', comma_format($board['posts']), ' ', $board['is_redirect'] ? $txt['redirects'] : $txt['posts'], ' <br />
							<span class="glyphicon ', $board['is_redirect'] ? '':'glyphicon-object-align-right','"  aria-hidden="true"></span>
							', $board['is_redirect'] ? '' : comma_format($board['topics']) . ' ' . $txt['board_topics'], '
							</p>
						</div><div class="uLastpost">';

				/* The board's and children's 'last_post's have:
				time, timestamp (a number that represents the time.), id (of the post), topic (topic id.),
				link, href, subject, start (where they should go for the first unread post.),
				and member. (which has id, name, link, href, username in it.) */
				if (!empty($board['last_post']['id']))
					echo '
						<p><strong>', $txt['last_post'], '</strong>  ', $txt['by'], ' ', $board['last_post']['member']['link'] , '<br />
						', $txt['in'], ' ', $board['last_post']['link'], '<br />
						', $txt['on'], ' ', $board['last_post']['time'],'
						</p>';
				echo '
					</div>
				</li>';
				//end tr
				// Show the "Child Boards: ". (there's a link_children but we're going to bold the new ones...)
				if (!empty($board['children']))
				{
					// Sort the links into an array with new boards bold so it can be imploded.
					$children = array();
					/* Each child in each board's children has:
							id, name, description, new (is it new?), topics (#), posts (#), href, link, and last_post. */
					foreach ($board['children'] as $child)
					{
						if (!$child['is_redirect'])
							$child['link'] = '<a href="' . $child['href'] . '" ' . ($child['new'] ? 'class="new_posts" ' : '') . 'title="' . ($child['new'] ? $txt['new_posts'] : $txt['old_posts']) . ' (' . $txt['board_topics'] . ': ' . comma_format($child['topics']) . ', ' . $txt['posts'] . ': ' . comma_format($child['posts']) . ')">' . $child['name'] . ($child['new'] ? '</a> <a href="' . $scripturl . '?action=unread;board=' . $child['id'] . '" title="' . $txt['new_posts'] . ' (' . $txt['board_topics'] . ': ' . comma_format($child['topics']) . ', ' . $txt['posts'] . ': ' . comma_format($child['posts']) . ')"><img src="' . $settings['lang_images_url'] . '/new.gif" class="new_posts" alt="" />' : '') . '</a>';
						else
							$child['link'] = '<a href="' . $child['href'] . '" title="' . comma_format($child['posts']) . ' ' . $txt['redirects'] . '">' . $child['name'] . '</a>';

						// Has it posts awaiting approval?
						if ($child['can_approve_posts'] && ($child['unapproved_posts'] || $child['unapproved_topics']))
							$child['link'] .= ' <a href="' . $scripturl . '?action=moderate;area=postmod;sa=' . ($child['unapproved_topics'] > 0 ? 'topics' : 'posts') . ';brd=' . $child['id'] . ';' . $context['session_var'] . '=' . $context['session_id'] . '" title="' . sprintf($txt['unapproved_posts'], $child['unapproved_topics'], $child['unapproved_posts']) . '" class="moderation_link">(!)</a>';

						$children[] = $child['new'] ? '<strong>' . $child['link'] . '</strong>' : $child['link'];
					}
					echo '
					<li id="board_', $board['id'], '_children" class="list-group-item list-group-item-success">						 
							<strong>', $txt['parent_boards'], '</strong>: ', implode(', ', $children), '						 
					</li>';
				}
			}
		echo '
			</ul>';
		}

		echo'</div>';
 
	}
	echo '
		</div>
	</div>';

	if ($context['user']['is_logged'])
		{
			echo '
		<div id="posting_icons" class="panel-body">';

			// Mark read button.
			$mark_read_button = array(
				'markread' => array('text' => 'mark_as_read', 'image' => 'markread.gif', 'lang' => true, 'url' => $scripturl . '?action=markasread;sa=all;' . $context['session_var'] . '=' . $context['session_id']),
			);
			// Show the mark all as read button?
			if ($settings['show_mark_read'] && !empty($context['categories']))
				echo '<div class="dropdown pull-right">
								<span  class="btn btn-default dropdown-toggle" id="dLabel"  data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">
								 '.$txt['uOpciones'].'
								<span class="caret"></span>
								</span>
							', template_button_strip($mark_read_button, ''), '
					 </div>';

			echo '
			<ul class="list-inline">
				<li class="floatleft"><img src="', $settings['images_url'], '/', $context['theme_variant_url'], 'new_some.png" alt="" /> ', $txt['new_posts'], '</li>
				<li class="floatleft"><img src="', $settings['images_url'], '/', $context['theme_variant_url'], 'new_none.png" alt="" /> ', $txt['old_posts'], '</li>
				<li class="floatleft"><img src="', $settings['images_url'], '/', $context['theme_variant_url'], 'new_redirect.png" alt="" /> ', $txt['redirect_board'], '</li>
			</ul>
		 </div>';


		}
	else
	{
			echo '
		<div id="posting_icons" class="flow_hidden panel-body">
			<ul class="list-inline">
				<li class="floatleft"><img src="', $settings['images_url'], '/new_none.png" alt="" /> ', $txt['old_posts'], '</li>
				<li class="floatleft"><img src="', $settings['images_url'], '/new_redirect.png" alt="" /> ', $txt['redirect_board'], '</li>
			</ul>
		</div>';
	}

	template_info_center();
}

function template_info_center()
{
	global $context, $settings, $options, $txt, $scripturl, $modSettings;

	// Here's where the "Info Center" starts...
	echo '	 
	
	<div class="panel panel-default">
		<h3 class="panel-body heading-h">
			<span class="pull-right"><img class="icon" id="upshrink_ic" src="', $settings['images_url'], '/collapse.gif" alt="*" title="', $txt['upshrink_description'], '" style="display: none;" /></span>
			', sprintf($txt['info_center_title'], $context['forum_name_html_safe']), '
		</h3>
	</div>
	<div class="***">
		<div class="row">
		<div id="upshrinkHeaderIC"', empty($options['collapse_header_ic']) ? '' : ' style="display: none;"', '>';

	// This is the "Recent Posts" bar.
	if (!empty($settings['number_recent_posts']) && (!empty($context['latest_posts']) || !empty($context['latest_post'])))
	{
		echo '<div class="col-xs-12 col-md-4">
		 <div class="list-group">
			 <div class="list-group-item list-group-item-default">
				<h4 class="panel-title">
					<span class="ie6_header floatleft">
						<a href="', $scripturl, '?action=recent"><span class="glyphicon glyphicon-bullhorn" aria-hidden="true"></span></a>
						', $txt['recent_posts'], '
					</span>
				</h4>
			 </div>
			<div id="recent_posts_content">
				<div class="entry-title" style="display: none;">', $context['forum_name_html_safe'], ' - ', $txt['recent_posts'], '</div>
				<div class="entry-content" style="display: none;">
					<a rel="author" href="', $scripturl, '?action=.xml;type=webslice">', $txt['subscribe_webslice'], '</a>
				</div></div>';

		// Only show one post.
		if ($settings['number_recent_posts'] == 1)
		{
			// latest_post has link, href, time, subject, short_subject (shortened with...), and topic. (its id.)
			echo '
				<strong><a href="', $scripturl, '?action=recent">', $txt['recent_posts'], '</a></strong>
				<p id="infocenter_onepost" class="middletext">
					', $txt['recent_view'], ' &quot;', $context['latest_post']['link'], '&quot; ', $txt['recent_updated'], ' (', $context['latest_post']['time'], ')<br />
				</p>';
		}
		// Show lots of posts.
		elseif (!empty($context['latest_posts']))
		{
			echo '
				<div id="ic_recentposts"  class="list-group">';

			/* Each post in latest_posts has:
					board (with an id, name, and link.), topic (the topic's id.), poster (with id, name, and link.),
					subject, short_subject (shortened with...), time, link, and href. */
			foreach ($context['latest_posts'] as $post)
				echo '
					 <div class="list-group-item"><span class="glyphicon glyphicon-comment" aria-hidden="true"></span> ', $post['link'], '<br />
					 <span class="glyphicon glyphicon-user" aria-hidden="true"></span> ', $txt['by'], ' ', $post['poster']['link'], ' <br />
					 <span class="glyphicon glyphicon-send" aria-hidden="true"></span> ', $post['board']['link'], '<span class="pull-right">', $post['time'], '</span>
					 </div>';
			echo '
				</div>';
		}
		echo '
			</div></div>';
	}
	echo'<div class="',!empty($settings['number_recent_posts'])? 'col-xs-12 col-md-8': 'col-xs-12 col-md-12',' fix-padding">';

	// Show information about events, birthdays, and holidays on the calendar.
		if ($context['show_calendar'])
		{
		echo '
		<div class="',!empty($settings['number_recent_posts'])? 'col-xs-12 col-md-12': 'col-xs-12 col-md-12','">
		<div class="panel panel-default">
			<div class="panel-heading">
				<h4>
					<span class="ie6_header floatleft">
						<a href="', $scripturl, '?action=calendar' . '"><span class="glyphicon glyphicon-calendar" aria-hidden="true"></span></a>
						', $context['calendar_only_today'] ? $txt['calendar_today'] : $txt['calendar_upcoming'], '
					</span>
				</h4>
			</div>
			<span class="panel-body">';

		// Holidays like "Christmas", "Chanukah", and "We Love [Unknown] Day" :P.
		if (!empty($context['calendar_holidays']))
				echo '
				<span class="holiday">', $txt['calendar_prompt'], ' ', implode(', ', $context['calendar_holidays']), '</span><br />';

		// People's birthdays. Like mine. And yours, I guess. Kidding.
		if (!empty($context['calendar_birthdays']))
		{
				echo '
				<span class="birthday">', $context['calendar_only_today'] ? $txt['birthdays'] : $txt['birthdays_upcoming'], '</span> ';
		/* Each member in calendar_birthdays has:
				id, name (person), age (if they have one set?), is_last. (last in list?), and is_today (birthday is today?) */
		foreach ($context['calendar_birthdays'] as $member)
				echo '
				<a href="', $scripturl, '?action=profile;u=', $member['id'], '">', $member['is_today'] ? '<strong>' : '', $member['name'], $member['is_today'] ? '</strong>' : '', isset($member['age']) ? ' (' . $member['age'] . ')' : '', '</a>', $member['is_last'] ? '<br />' : ', ';
		}
		// Events like community get-togethers.
		if (!empty($context['calendar_events']))
		{
			echo '
				<span class="event">', $context['calendar_only_today'] ? $txt['events'] : $txt['events_upcoming'], '</span> ';
			/* Each event in calendar_events should have:
					title, href, is_last, can_edit (are they allowed?), modify_href, and is_today. */
			foreach ($context['calendar_events'] as $event)
				echo '
					', $event['can_edit'] ? '<a href="' . $event['modify_href'] . '" title="' . $txt['calendar_edit'] . '"><img src="' . $settings['images_url'] . '/icons/modify_small.gif" alt="*" /></a> ' : '', $event['href'] == '' ? '' : '<a href="' . $event['href'] . '">', $event['is_today'] ? '<strong>' . $event['title'] . '</strong>' : $event['title'], $event['href'] == '' ? '' : '</a>', $event['is_last'] ? '<br />' : ', ';
		}
		echo '
			</span></div></div>';
		}

	// Show statistical style information...
	if ($settings['show_stats_index'])
	{
		echo '<div class="',!empty($settings['number_recent_posts'])? 'col-xs-12 col-md-12': 'col-xs-12 col-md-6','">

				<div class="panel panel-default">
				<div class="panel-heading">
					<h4>
						<span class="ie6_header floatleft">
							<a href="', $scripturl, '?action=stats"><span class="glyphicon glyphicon-stats" aria-hidden="true"></span></a>
							', $txt['forum_stats'], '
						</span>
					</h4>
				</div>
				<div class="panel-body">
					', $context['common_stats']['total_posts'], ' ', $txt['posts_made'], ' ', $txt['in'], ' ', $context['common_stats']['total_topics'], ' ', $txt['topics'], ' ', $txt['by'], ' ', $context['common_stats']['total_members'], ' ', $txt['members'], '. ', !empty($settings['show_latest_member']) ? $txt['latest_member'] . ': <strong> ' . $context['common_stats']['latest_member']['link'] . '</strong>' : '', '<br />
					', (!empty($context['latest_post']) ? $txt['latest_post'] . ': <strong>&quot;' . $context['latest_post']['link'] . '&quot;</strong>  ( ' . $context['latest_post']['time'] . ' )<br />' : ''), '
					<a href="', $scripturl, '?action=recent">', $txt['recent_view'], '</a>', $context['show_stats'] ? '<br />
					<a href="' . $scripturl . '?action=stats">' . $txt['more_stats'] . '</a>' : '', '
				</div>
				</div>
			</div>';
	}

	// "Users online" - in order of activity.
	echo '<div class="',!empty($settings['number_recent_posts'])? 'col-xs-12 col-md-12': 'col-xs-12 col-md-6','">
		<div class="panel panel-default">
			<div class="panel-heading">
				<h4 class="titlebg">
					<span class="ie6_header floatleft">
						', $context['show_who'] ? '<a href="' . $scripturl . '?action=who' . '">' : '', '<span class="glyphicon glyphicon-record" aria-hidden="true"></span>', $context['show_who'] ? '</a>' : '', '
						', $txt['online_users'], '
					</span>
				</h4>
			</div>
			<div class="panel-body">
				', $context['show_who'] ? '<a href="' . $scripturl . '?action=who">' : '', comma_format($context['num_guests']), ' ', $context['num_guests'] == 1 ? $txt['guest'] : $txt['guests'], ', ' . comma_format($context['num_users_online']), ' ', $context['num_users_online'] == 1 ? $txt['user'] : $txt['users'];

	// Handle hidden users and buddies.
	$bracketList = array();
	if ($context['show_buddies'])
		$bracketList[] = comma_format($context['num_buddies']) . ' ' . ($context['num_buddies'] == 1 ? $txt['buddy'] : $txt['buddies']);
	if (!empty($context['num_spiders']))
		$bracketList[] = comma_format($context['num_spiders']) . ' ' . ($context['num_spiders'] == 1 ? $txt['spider'] : $txt['spiders']);
	if (!empty($context['num_users_hidden']))
		$bracketList[] = comma_format($context['num_users_hidden']) . ' ' . $txt['hidden'];

	if (!empty($bracketList))
		echo ' (' . implode(', ', $bracketList) . ')';

	echo $context['show_who'] ? '</a>' : '', '			
			<p class="inline smalltext">';

	// Assuming there ARE users online... each user in users_online has an id, username, name, group, href, and link.
	//if (!empty($context['users_online']))
	//{
		echo '
				', sprintf($txt['users_active'], $modSettings['lastActive']), ':<br />', implode(', ', $context['list_users_online']);

		// Showing membergroups?
		if (!empty($settings['show_group_key']) && !empty($context['membergroups']))
			echo '
				<br />[' . implode(']&nbsp;&nbsp;[', $context['membergroups']) . ']';
	//}

	echo '
			</p>
			<p class="last smalltext">
				', $txt['most_online_today'], ': <strong>', comma_format($modSettings['mostOnlineToday']), '</strong>.
				', $txt['most_online_ever'], ': ', comma_format($modSettings['mostOnline']), ' (', timeformat($modSettings['mostDate']), ')
			</p></div></div></div>';

	// If they are logged in, but statistical information is off... show a personal message bar.
		if ($context['user']['is_logged'] && !$settings['show_stats_index'])
		{
		echo '
		<div class="',!empty($settings['number_recent_posts'])? 'col-xs-12 col-md-12': 'col-xs-12 col-md-12','">
		<div class="panel panel-default">
			<div class="panel-heading">
				<h4>
					<span class="ie6_header floatleft">
						', $context['allow_pm'] ? '<a href="' . $scripturl . '?action=pm">' : '', '<span class="glyphicon glyphicon-envelope" aria-hidden="true"></span>', $context['allow_pm'] ? '</a>' : '', '
						<span>', $txt['personal_message'], '</span>
					</span>
				</h4>
			</div>
			<div class="panel-body">
				<strong><a href="', $scripturl, '?action=pm">', $txt['personal_message'], '</a></strong>
				<span class="smalltext">
					', $txt['you_have'], ' ', comma_format($context['user']['messages']), ' ', $context['user']['messages'] == 1 ? $txt['message_lowercase'] : $txt['msg_alert_messages'], '.... ', $txt['click'], ' <a href="', $scripturl, '?action=pm">', $txt['here'], '</a> ', $txt['to_view'], '
				</span>
			</div></div></div>';
		}

	echo '</div>
		</div>
	</div></div>
	<span class="lowerframe"><span></span></span>';

	// Info center collapse object.
	echo '
	<script type="text/javascript"><!-- // --><![CDATA[
		var oInfoCenterToggle = new smc_Toggle({
			bToggleEnabled: true,
			bCurrentlyCollapsed: ', empty($options['collapse_header_ic']) ? 'false' : 'true', ',
			aSwappableContainers: [
				\'upshrinkHeaderIC\'
			],
			aSwapImages: [
				{
					sId: \'upshrink_ic\',
					srcExpanded: smf_images_url + \'/collapse.gif\',
					altExpanded: ', JavaScriptEscape($txt['upshrink_description']), ',
					srcCollapsed: smf_images_url + \'/expand.gif\',
					altCollapsed: ', JavaScriptEscape($txt['upshrink_description']), '
				}
			],
			oThemeOptions: {
				bUseThemeSettings: ', $context['user']['is_guest'] ? 'false' : 'true', ',
				sOptionName: \'collapse_header_ic\',
				sSessionVar: ', JavaScriptEscape($context['session_var']), ',
				sSessionId: ', JavaScriptEscape($context['session_id']), '
			},
			oCookieOptions: {
				bUseCookie: ', $context['user']['is_guest'] ? 'true' : 'false', ',
				sCookieName: \'upshrinkIC\'
			}
		});
	// ]]></script>';
}
?>