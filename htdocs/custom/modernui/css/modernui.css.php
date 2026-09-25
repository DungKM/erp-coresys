<?php
/* Copyright (C) 2026 NSIS
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <https://www.gnu.org/licenses/>.
 */

/**
 * \file       htdocs/custom/modernui/css/modernui.css.php
 * \ingroup    modernui
 * \brief      Modern skin loaded after the theme stylesheet (eldy or md).
 *
 * This file does NOT bootstrap Dolibarr (no main.inc.php) so it is fast and cacheable.
 * It only reads the parameters Dolibarr appends to module css urls:
 *   theme=md|eldy, optioncss=print, dol_hide_topmenu, dol_hide_leftmenu
 *
 * Section order: tokens > base > layout > components > pages > responsive > dark mode
 */

header('Content-type: text/css; charset=UTF-8');
// Revalidate on every page: the browser gets a cheap 304 until this file changes, then the new CSS at once
$etag = '"mui-'.md5(filemtime(__FILE__).'|'.(isset($_SERVER['QUERY_STRING']) ? $_SERVER['QUERY_STRING'] : '')).'"';
header('Cache-Control: no-cache, public');
header('ETag: '.$etag);
header('Last-Modified: '.gmdate('D, d M Y H:i:s', filemtime(__FILE__)).' GMT');
if (isset($_SERVER['HTTP_IF_NONE_MATCH']) && trim($_SERVER['HTTP_IF_NONE_MATCH']) === $etag) {
	http_response_code(304);
	exit;
}

$theme = (isset($_GET['theme']) && preg_match('/^[a-z0-9_]+$/i', $_GET['theme'])) ? $_GET['theme'] : 'eldy';
$isprint = (isset($_GET['optioncss']) && $_GET['optioncss'] == 'print');
$hidetop = !empty($_GET['dol_hide_topmenu']);
$hideleft = !empty($_GET['dol_hide_leftmenu']);
$ismd = ($theme == 'md');

// Printing must keep the native (paper friendly) rendering
if ($isprint) {
	print "/* modernui: print mode, nothing overridden */\n";
	exit;
}

$fontdir = '../fonts/';
?>
/* ==========================================================================
   ModernUI for Dolibarr — theme detected: <?php echo $theme; ?>

   ========================================================================== */

/* --------------------------------------------------------------------------
   0. Fonts (bundled, works offline) — Be Vietnam Pro, latin + vietnamese
   -------------------------------------------------------------------------- */
<?php
foreach (array(400, 500, 600, 700) as $w) {
	$subsets = array(
		'vietnamese' => 'U+0102-0103,U+0110-0111,U+0128-0129,U+0168-0169,U+01A0-01A1,U+01AF-01B0,U+0300-0301,U+0303-0304,U+0308-0309,U+0323,U+0329,U+1EA0-1EF9,U+20AB',
		'latin-ext' => 'U+0100-02BA,U+02BD-02C5,U+02C7-02CC,U+02CE-02D7,U+02DD-02FF,U+0304,U+0308,U+0329,U+1D00-1DBF,U+1E00-1E9F,U+1EF2-1EFF,U+2020,U+20A0-20AB,U+20AD-20C0,U+2113,U+2C60-2C7F,U+A720-A7FF',
		'latin' => 'U+0000-00FF,U+0131,U+0152-0153,U+02BB-02BC,U+02C6,U+02DA,U+02DC,U+0304,U+0308,U+0329,U+2000-206F,U+20AC,U+2122,U+2191,U+2193,U+2212,U+2215,U+FEFF,U+FFFD',
	);
	foreach ($subsets as $subset => $range) {
		print "@font-face{font-family:'Be Vietnam Pro';font-style:normal;font-display:swap;font-weight:".$w.";";
		print "src:url('".$fontdir."be-vietnam-pro-".$subset."-".$w."-normal.woff2') format('woff2');unicode-range:".$range.";}\n";
	}
}
?>

/* --------------------------------------------------------------------------
   1. Design tokens — change colors here only
   -------------------------------------------------------------------------- */
:root {
	--primary: #4F46E5;
	--primary-hover: #4338CA;
	--primary-soft: #EEF2FF;
	--primary-ring: rgba(79, 70, 229, .18);
	--accent: #06B6D4;
	--success: #10B981;
	--success-soft: #ECFDF5;
	--warning: #F59E0B;
	--warning-soft: #FFFBEB;
	--danger: #EF4444;
	--danger-soft: #FEF2F2;
	--info: #3B82F6;
	--info-soft: #EFF6FF;

	--bg-app: #F8FAFC;
	--bg-surface: #FFFFFF;
	--bg-sidebar: #FFFFFF;
	--bg-hover: #F1F5F9;
	--bg-subtle: #F8FAFC;
	--border: #E2E8F0;
	--border-strong: #CBD5E1;
	--text-primary: #0F172A;
	--text-secondary: #475569;
	--text-muted: #94A3B8;

	/* Module family colors (icon / soft background) */
	--fam-hr: #8B5CF6;       --fam-hr-bg: #F5F3FF;
	--fam-crm: #3B82F6;      --fam-crm-bg: #EFF6FF;
	--fam-fin: #10B981;      --fam-fin-bg: #ECFDF5;
	--fam-prod: #F97316;     --fam-prod-bg: #FFF7ED;
	--fam-proj: #EC4899;     --fam-proj-bg: #FDF2F8;
	--fam-other: #64748B;    --fam-other-bg: #F1F5F9;

	--font: "Be Vietnam Pro", "Inter", system-ui, -apple-system, "Segoe UI", Roboto, Arial, sans-serif;
	--fs-body: 14px;
	--fs-small: 12px;

	--sp-1: 4px; --sp-2: 8px; --sp-3: 12px; --sp-4: 16px; --sp-5: 24px; --sp-6: 32px; --sp-7: 48px;

	--radius-control: 8px;
	--radius-card: 12px;
	--radius-modal: 16px;
	--radius-full: 999px;

	--shadow-sm: 0 1px 2px rgba(15, 23, 42, .06);
	--shadow-md: 0 4px 12px rgba(15, 23, 42, .08);
	--shadow-lg: 0 12px 32px rgba(15, 23, 42, .12);
	--transition: all .18s ease;

	--topbar-h: 60px;
	--sidebar-w: 260px;
	--control-h: 38px;

	/* Re-map the native Dolibarr theme variables onto the design system */
	--colorbackhmenu1: var(--bg-surface);
	--colorbackvmenu1: var(--bg-sidebar);
	--colorbacktitle1: var(--bg-subtle);
	--colorbacktabcard1: var(--bg-surface);
	--colorbacktabactive: var(--bg-surface);
	--colorbacklineimpair1: var(--bg-surface);
	--colorbacklineimpair2: var(--bg-surface);
	--colorbacklinepair1: var(--bg-surface);
	--colorbacklinepair2: var(--bg-surface);
	--colorbacklinepairhover: var(--bg-subtle);
	--colorbacklinepairchecked: var(--primary-soft);
	--colorbacklinebreak: var(--bg-subtle);
	--colorbackbody: var(--bg-app);
	--colortexttitlenotab: var(--text-primary);
	--colortexttitlenotab2: var(--text-primary);
	--colortexttitle: var(--text-secondary);
	--colortexttitlelink: var(--text-primary);
	--colortext: var(--text-primary);
	--colortextlink: var(--primary);
	--colortextbackhmenu: var(--text-secondary);
	--colortextbackvmenu: var(--text-secondary);
	--colortopbordertitle1: var(--border);
	--colortextbacktab: var(--text-primary);
	--colorboxiconbg: var(--primary-soft);
	--colorboxstatsborder: var(--border);
	--inputbordercolor: var(--border);
	--inputbackgroundcolor: var(--bg-surface);
	--tooltipbgcolor: var(--bg-surface);
	--tooltipfontcolor: var(--text-primary);
	--butactionbg: var(--primary);
	--textbutaction: #fff;
	--listetotal: var(--primary);
	--refidnocolor: var(--text-secondary);
	--tableforfieldcolor: var(--text-secondary);
	--fieldrequiredcolor: var(--text-primary);
	--infoboxmoduleenabledbgcolor: var(--bg-surface);
}

/* --------------------------------------------------------------------------
   2. Base & typography
   -------------------------------------------------------------------------- */
html { -webkit-text-size-adjust: 100%; }
body, body.body {
	font-family: var(--font) !important;
	font-size: var(--fs-body);
	line-height: 1.5;
	color: var(--text-primary);
	background: var(--bg-app);
	-webkit-font-smoothing: antialiased;
	-moz-osx-font-smoothing: grayscale;
	text-rendering: optimizeLegibility;
}
body, button, input, select, textarea, .ui-widget, .ui-widget input, .ui-widget select, .ui-widget textarea, .ui-widget button,
a:link, a:visited, a:hover, a:active, .classlink,
a.vmenu:link, a.vmenu:visited, a.vsmenu:link, a.vsmenu:visited, span.vmenu, span.vsmenu,
.mainmenuaspan, a.tab:link, a.tab:visited, a.tab:hover, a.tab#active, .side-nav,
div.blockvmenupair, div.blockvmenuimpair, div.blockvmenusearch, div.blockvmenubookmarks {
	font-family: var(--font) !important;
}
/* Keep icon fonts untouched */
.fa, .fas, .far, .fal, .fab, [class^="fa-"], [class*=" fa-"] { font-family: "Font Awesome 5 Free"; }
.fab { font-family: "Font Awesome 5 Brands" !important; }

a:link, a:visited, a:active, .classlink { color: var(--primary); }
a:hover { color: var(--primary-hover); text-decoration: none; }

.opacitymedium { opacity: 1; color: var(--text-muted); }
.opacitymediumbycolor { color: var(--text-muted) !important; }
.opacityhigh { opacity: .6; }
hr { border: 0; border-top: 1px solid var(--border); }

::placeholder { color: var(--text-muted) !important; opacity: 1; }

/* Focus visible (accessibility) */
a:focus-visible, button:focus-visible, .button:focus-visible, .butAction:focus-visible, [tabindex]:focus-visible {
	outline: 2px solid var(--primary);
	outline-offset: 2px;
	border-radius: 6px;
}

/* Thin scrollbars */
* { scrollbar-width: thin; scrollbar-color: var(--border-strong) transparent; }
::-webkit-scrollbar { width: 8px; height: 8px; }
::-webkit-scrollbar-track { background: transparent; }
::-webkit-scrollbar-thumb { background: var(--border-strong); border-radius: var(--radius-full); border: 2px solid transparent; background-clip: padding-box; }
::-webkit-scrollbar-thumb:hover { background: var(--text-muted); background-clip: padding-box; }

::selection { background: var(--primary-soft); color: var(--primary-hover); }

<?php if (!$hidetop) { ?>
/* --------------------------------------------------------------------------
   3. Layout — Top navigation bar
   -------------------------------------------------------------------------- */
#id-top, header#id-top.side-nav-vert {
	position: sticky;
	top: 0;
	z-index: 1005;
	margin-left: 0 !important;
	margin-right: 0 !important;
	height: var(--topbar-h);
	background: var(--bg-surface) !important;
	border-bottom: 1px solid var(--border) !important;
	box-shadow: var(--shadow-sm);
}
div#tmenu_tooltip, div#tmenu_tooltipinvert {
	background: transparent !important;
	height: var(--topbar-h);
	padding-left: calc(var(--sidebar-w) + 12px) !important;
	padding-right: 340px !important; /* room for the user block on the right */
	box-sizing: border-box;
	overflow: hidden;
}
div.tmenudiv {
	height: var(--topbar-h);
	overflow-x: auto;
	overflow-y: hidden;
	scrollbar-width: none;
}
div.tmenudiv::-webkit-scrollbar { display: none; }
ul.tmenu {
	display: flex !important;
	align-items: center;
	gap: 2px;
	height: var(--topbar-h);
	margin: 0 !important;
	padding: 0 !important;
}
ul.tmenu li, ul.tmenu li.tmenu, ul.tmenu li.tmenusel {
	background: transparent !important;
	float: none !important;
	height: auto !important;
	min-width: 0 !important;
	padding: 0 !important;
	margin: 0 !important;
	opacity: 1 !important;
	position: relative;
	flex: 0 0 auto;
	width: auto !important;
}
li.tmenusel::after, li.tmenusel:hover::after { display: none !important; }
li.tmenu:hover { opacity: 1 !important; }
li#mainmenutd_home { margin-left: 0 !important; }
li.tmenucompanylogo, li#mainmenutd_companylogo { display: none !important; } /* logo moved to the brand block */
.tmenuend { display: none !important; }

ul.tmenu div.tmenucenter {
	display: flex !important;
	align-items: center;
	gap: 8px;
	height: 40px !important;
	width: auto !important;
	min-width: 0 !important;
	max-width: none !important;
	padding: 0 12px !important;
	border-radius: var(--radius-control);
	color: var(--text-secondary);
	transition: var(--transition);
	position: relative;
}
ul.tmenu li:hover div.tmenucenter { background: var(--bg-hover); color: var(--text-primary); }
ul.tmenu li.tmenusel div.tmenucenter { background: var(--primary-soft); color: var(--primary); }
ul.tmenu li.tmenusel div.tmenucenter::after {
	content: "";
	position: absolute;
	left: 12px; right: 12px;
	bottom: -10px;
	height: 2px;
	border-radius: 2px;
	background: var(--primary);
}
a.tmenuimage, div.tmenuimage { display: flex !important; align-items: center; width: auto !important; min-width: 0 !important; }
div.mainmenu, div.topmenuimage {
	height: auto !important;
	min-width: 0 !important;
	width: auto !important;
	background-position: center center !important;
	position: static !important;
	top: auto !important;
	left: auto !important;
}
.mainmenu::before, .mainmenu span::before {
	font-size: 15px !important;
	line-height: 20px !important;
	width: 18px;
	display: inline-block;
}
div.mainmenu, .mainmenu span, ul.tmenu .tmenuimage span { color: var(--text-muted); transition: var(--transition); }
ul.tmenu li:hover div.mainmenu, ul.tmenu li:hover .mainmenu span { color: var(--text-secondary); }
ul.tmenu li.tmenusel div.mainmenu, ul.tmenu li.tmenusel .mainmenu span { color: var(--primary) !important; }
div.mainmenu[style*="background-image"], div.mainmenu.tmenuimageforpng { width: 18px !important; height: 18px !important; background-size: contain !important; }

a.tmenu:link, a.tmenu:visited, a.tmenu:hover, a.tmenu:active,
a.tmenusel:link, a.tmenusel:visited, a.tmenusel:hover, a.tmenusel:active,
a.tmenulabel {
	padding: 0 !important;
	color: inherit !important;
	text-decoration: none !important;
	display: inline-flex;
	align-items: center;
}
.mainmenuaspan {
	display: inline-block !important;
	font-size: 13px !important;
	font-weight: 500 !important;
	opacity: 1 !important;
	padding: 0 !important;
	max-width: 118px;
	overflow: hidden;
	text-overflow: ellipsis;
	white-space: nowrap;
	vertical-align: middle;
	line-height: 20px;
}
li.tmenusel .mainmenuaspan { font-weight: 600 !important; }
.tmenudisabled, a.tmenudisabled, span.mainmenuaspan.tmenudisabled { opacity: .45 !important; }

/* ‹ › scroll buttons (added by JS when the entries overflow) */
div#tmenu_tooltip, div#tmenu_tooltipinvert { position: relative; }
.mui-tscroll {
	position: absolute;
	top: calc((var(--topbar-h) - 36px) / 2);
	z-index: 3;
	width: 36px;
	height: 36px;
	display: none;
	align-items: center;
	justify-content: center;
	padding: 0;
	border: 1px solid var(--border);
	border-radius: 50%;
	background: var(--bg-surface);
	color: var(--text-secondary);
	box-shadow: var(--shadow-md);
	cursor: pointer;
	font-size: 12px;
	transition: var(--transition);
}
.mui-tscroll:hover { color: var(--primary); border-color: #C7D2FE; background: var(--primary-soft); }
.mui-tscroll-on .mui-tscroll { display: inline-flex; }
.mui-tscroll-on.mui-at-start .mui-tscroll-prev, .mui-tscroll-on.mui-at-end .mui-tscroll-next { display: none; }
/* soft fade on the clipped side */
.mui-tscroll-on div.tmenudiv {
	-webkit-mask-image: linear-gradient(to right, transparent 0, #000 48px, #000 calc(100% - 48px), transparent 100%);
	mask-image: linear-gradient(to right, transparent 0, #000 48px, #000 calc(100% - 48px), transparent 100%);
	scroll-behavior: smooth;
}
.mui-tscroll-on.mui-at-start div.tmenudiv {
	-webkit-mask-image: linear-gradient(to right, #000 calc(100% - 48px), transparent 100%);
	mask-image: linear-gradient(to right, #000 calc(100% - 48px), transparent 100%);
}
.mui-tscroll-on.mui-at-end div.tmenudiv {
	-webkit-mask-image: linear-gradient(to right, transparent 0, #000 48px);
	mask-image: linear-gradient(to right, transparent 0, #000 48px);
}
.mui-tscroll-on.mui-at-start.mui-at-end div.tmenudiv { -webkit-mask-image: none; mask-image: none; }

/* Menu hider (hamburger) from Dolibarr */
li.menuhider div.tmenucenter { padding: 0 10px !important; gap: 0; }
li.menuhider a.tmenulabel, li.menuhider span.tmenulabel, li.menuhider .mainmenuaspan { display: none !important; }

/* ---------- User block (right side of the top bar) ---------- */
div.login_block, div.login_block.usedropdown {
	position: fixed !important;
	top: 0 !important;
	right: 12px !important;
	left: auto !important;
	width: auto !important;
	height: var(--topbar-h) !important;
	padding: 0 !important;
	margin: 0 !important;
	background: transparent !important;
	border: 0 !important;
	z-index: 1010 !important;
	display: flex !important;
	flex-direction: row;
	align-items: center;
	gap: 4px;
	line-height: normal !important;
	text-align: left;
	float: none !important;
}
div.login_block > div, div.login_block_other, div.login_block_tools, div.login_block_user, .topnav div.login_block_user, .topnav div.login_block_other {
	display: flex !important;
	align-items: center;
	float: none !important;
	clear: none !important;
	padding: 0 !important;
	margin: 0 !important;
	height: auto !important;
	line-height: normal !important;
	max-width: none !important;
	position: static !important;
}
div.login_block_tools > div, div.login_block_tools > div.inline-block { display: flex !important; align-items: center; gap: 2px; position: static !important; right: auto !important; }
div.login_block_other { order: 1; gap: 2px; }
div.login_block_tools { order: 2; }
div.login_block_user { order: 3; flex: 0 0 auto; width: auto !important; margin-left: 6px !important; padding-left: 10px !important; border-left: 1px solid var(--border) !important; }
div.login_block_user .login_block_elem_name, div.login_block_user .centpercent { width: auto !important; }
div#topmenu-login-dropdown, div.login_block #topmenu-login-dropdown { position: relative !important; top: auto !important; right: auto !important; left: auto !important; }
div.login_block_tools:empty, div.login_block_tools > div:empty { display: none !important; }
div.login_block_other > div.inline-block { display: inline-flex !important; }
.login_block_elem, div.login_block_other .login_block_elem {
	float: none !important;
	height: auto !important;
	line-height: normal !important;
	padding: 0 !important;
	display: inline-flex !important;
	align-items: center;
}
/* Icon buttons (help, print, bookmarks, AI, search...) */
div.login_block a, div.login_block .dropdown-toggle, div.login_block_tools .dropdown > a {
	color: var(--text-secondary) !important;
}
div.login_block_other .login_block_elem a,
div.login_block_tools a.dropdown-toggle,
div.login_block_tools .login-dropdown-a,
div.login_block_tools > div > div > a {
	display: inline-flex !important;
	align-items: center;
	justify-content: center;
	width: 36px;
	height: 36px;
	border-radius: var(--radius-control);
	transition: var(--transition);
	text-decoration: none !important;
}
div.login_block_other .login_block_elem a:hover,
div.login_block_tools a.dropdown-toggle:hover,
div.login_block_tools .login-dropdown-a:hover,
div.login_block_tools > div > div > a:hover { background: var(--bg-hover); color: var(--text-primary) !important; }
span.fa.atoplogin, span.fas.atoplogin, .atoplogin.fa, div.login_block .fa, div.login_block .fas, div.login_block .far {
	font-size: 16px !important;
	color: var(--text-secondary) !important;
	text-decoration: none !important;
	opacity: 1 !important;
}
.atoplogin:hover { text-decoration: none !important; }
.helppresentcircle { display: none !important; }
/* Version pill */
span.aversion, div.login_block span.aversion {
	filter: none !important;
	color: var(--text-muted) !important;
	background: var(--bg-hover);
	border-radius: var(--radius-full);
	padding: 2px 8px;
	font-size: 11px !important;
	font-weight: 500;
	white-space: nowrap;
}
a.aversion { width: auto !important; }
/* The version is already shown in the brand block */
html.mui div.login_block_other .login_block_elem:has(span.aversion) { display: none !important; }
/* Avatar + name */
div.login_block_user .login_block_elem_name { display: inline-flex !important; align-items: center; }
div.login_block_user .dropdown-toggle, div.login_block_user a.dropdown-toggle.login-dropdown-a {
	display: inline-flex !important;
	align-items: center;
	gap: 8px;
	height: 40px;
	padding: 0 8px 0 4px !important;
	border-radius: var(--radius-full);
	transition: var(--transition);
	text-decoration: none !important;
}
div.login_block_user .dropdown-toggle:hover { background: var(--bg-hover); }
.userimg.atoplogin img.userphoto, .userimgatoplogin img.userphoto,
.userimg.atoplogin span.userphoto, .userimgatoplogin span.userphoto {
	width: 32px !important;
	height: 32px !important;
	border-radius: 50% !important;
	object-fit: cover;
	box-shadow: 0 0 0 2px var(--bg-surface), 0 0 0 3px var(--border);
}
.userimg.atoplogin span.userphoto::before, .userimgatoplogin span.userphoto::before { margin-top: 0 !important; }
/* No photo: Dolibarr prints a fa-user icon → show it as a soft avatar */
.userimg.atoplogin span.userphoto.fas, .userimgatoplogin span.userphoto.fas, div.login_block span.photouserphoto.fas {
	display: inline-flex !important;
	align-items: center;
	justify-content: center;
	background: var(--primary-soft) !important;
	color: var(--primary) !important;
	font-size: 14px !important;
	box-shadow: none;
}
div.login_block .login-dropdown-a span.fas.userphoto::before { color: var(--primary); }
div.login_block a .atoploginusername, .atoploginusername {
	max-width: 140px !important;
	display: inline-block !important;
	overflow: hidden;
	text-overflow: ellipsis;
	white-space: nowrap;
	color: var(--text-primary) !important;
	font-size: 13px;
	font-weight: 500;
	vertical-align: middle;
}
div.login_block_user .dropdown-toggle::after {
	content: "\f107";
	font-family: "Font Awesome 5 Free";
	font-weight: 900;
	font-size: 11px;
	color: var(--text-muted);
	margin-left: 2px;
}
<?php } // end !hidetop ?>

<?php if (!$hideleft) { ?>
/* --------------------------------------------------------------------------
   4. Layout — Brand block + left sidebar
   -------------------------------------------------------------------------- */
.mui-brand {
	position: fixed;
	top: 0;
	left: 0;
	width: var(--sidebar-w);
	height: var(--topbar-h);
	z-index: 1006;
	display: flex;
	align-items: center;
	gap: 10px;
	padding: 0 20px;
	box-sizing: border-box;
	background: var(--bg-sidebar);
	border-right: 1px solid var(--border);
	border-bottom: 1px solid var(--border);
	text-decoration: none !important;
	color: var(--text-primary) !important;
}
.mui-brand:hover { text-decoration: none !important; }
.mui-brand-mark {
	flex: 0 0 32px;
	width: 32px;
	height: 32px;
	border-radius: 9px;
	background: linear-gradient(135deg, var(--primary), var(--accent));
	color: #fff;
	display: flex;
	align-items: center;
	justify-content: center;
	font-weight: 700;
	font-size: 15px;
	box-shadow: var(--shadow-sm);
}
.mui-brand-logo { max-height: 32px; max-width: 120px; object-fit: contain; border-radius: 6px; }
.mui-brand-name { font-size: 15px; font-weight: 700; letter-spacing: -.01em; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.mui-brand-version {
	margin-left: auto;
	font-size: 11px;
	font-weight: 500;
	color: var(--text-muted);
	background: var(--bg-hover);
	border-radius: var(--radius-full);
	padding: 2px 8px;
	white-space: nowrap;
}
<?php if ($ismd) { ?>
body.sidebar-collapse .mui-brand { width: auto; border-right: 0; }
body.sidebar-collapse .mui-brand .mui-brand-version { display: none; }
<?php } ?>

.side-nav, #id-left {
	background: var(--bg-sidebar) !important;
	box-shadow: none !important;
	border-right: 1px solid var(--border) !important;
	font-family: var(--font) !important;
	color: var(--text-secondary);
}
.side-nav {
	position: fixed !important;
	top: var(--topbar-h) !important;
	bottom: 0;
	left: 0;
	width: var(--sidebar-w) !important;
	overflow-y: auto !important;
	overflow-x: hidden !important;
	z-index: 1000 !important;
	box-sizing: border-box;
}
.side-nav #id-left, #id-left {
	display: block !important; /* native is table-cell, which lets wide content stretch it */
	width: 100% !important;
	max-width: var(--sidebar-w);
	padding: 12px 12px 32px !important;
	box-sizing: border-box;
	min-height: 100%;
	border-right: 0 !important;
	overflow: hidden;
}
div.vmenu, td.vmenu, .vmenu {
	width: 100% !important;
	margin: 0 !important;
	float: none !important;
	padding: 0 !important;
}
.vmenudisabled { margin-left: 0 !important; }

/* Search box */
div.blockvmenusearch, div.blockvmenubookmarks {
	background: transparent !important;
	padding: 4px 0 12px !important;
	margin: 0 !important;
	border: 0 !important;
	position: relative;
}
div.blockvmenusearch .select2-container, div.blockvmenusearch select, .vmenusearchselectcombo { width: 100% !important; }
div.blockvmenusearch .select2-container--default .select2-selection--single,
.vmenusearchselectcombo.select2-selection, span.select2-selection.vmenusearchselectcombo {
	height: 38px !important;
	border: 0 !important;
	border-radius: var(--radius-control) !important;
	background: var(--bg-hover) !important;
	padding-left: 12px !important;
	box-shadow: none !important;
	transition: var(--transition);
}
div.blockvmenusearch .select2-container--focus .select2-selection--single,
div.blockvmenusearch .select2-container--open .select2-selection--single {
	background: var(--bg-surface) !important;
	box-shadow: 0 0 0 1px var(--primary), 0 0 0 4px var(--primary-ring) !important;
}
div.blockvmenusearch .select2-container { max-width: 100%; box-sizing: border-box; }
div.blockvmenusearch .select2-selection__rendered { line-height: 38px !important; color: var(--text-muted) !important; font-size: 13px; padding-left: 0 !important; padding-right: 60px !important; }
div.blockvmenusearch .select2-selection__rendered .fa, div.blockvmenusearch .select2-selection__placeholder .fa, div.blockvmenusearch .select2-selection__rendered .fas { color: var(--text-muted) !important; opacity: 1 !important; margin-right: 6px; }
div.blockvmenusearch .select2-selection__arrow { display: none !important; }
.mui-kbd {
	position: absolute;
	right: 10px;
	top: 13px;
	z-index: 2;
	font-size: 11px;
	font-weight: 500;
	color: var(--text-muted);
	background: var(--bg-surface);
	border: 1px solid var(--border);
	border-radius: 6px;
	padding: 1px 6px;
	pointer-events: none;
	font-family: var(--font);
}
div.blockvmenusearch input.inputsearch, .searchform input[type="text"] {
	width: 100% !important;
	height: 38px;
	border: 0 !important;
	border-radius: var(--radius-control) !important;
	background: var(--bg-hover) !important;
	padding-left: 32px !important;
}

/* Menu blocks: no heavy separators, spacing instead */
div.blockvmenu, div.blockvmenupair, div.blockvmenuimpair, div.blockvmenufirst, div.blockvmenulast {
	background: transparent !important;
	border: 0 !important;
	border-top: 0 !important;
	margin: 0 0 16px 0 !important;
	padding: 0 !important;
	box-shadow: none !important;
}
div.blockvmenuend { display: none !important; }
div.menu_top, div.menu_end { display: none !important; }

/* Level 0 */
div.menu_titre {
	padding: 0 !important;
	margin: 0 0 2px 0;
	overflow: visible !important;
}
div.menu_titre a.vmenu, div.menu_titre span.vmenu, div.menu_titre span.vmenudisabled {
	display: flex !important;
	align-items: center;
	gap: 10px;
	margin: 0 !important;
	padding: 8px 12px !important;
	border-radius: var(--radius-control);
	font-size: 13px !important;
	font-weight: 600 !important;
	color: var(--text-primary) !important;
	white-space: nowrap;
	overflow: hidden;
	text-overflow: ellipsis;
	text-decoration: none !important;
	transition: var(--transition);
	line-height: 20px;
	box-sizing: border-box;
	width: 100% !important;
	max-width: 100%;
}
div.menu_contenu a.vsmenu, div.menu_contenu span.vsmenu { box-sizing: border-box; max-width: 100%; }
div.menu_titre a.vmenu:hover { background: var(--bg-hover); }
div.menu_titre span.vmenudisabled { color: var(--text-muted) !important; }
div.menu_titre .pictofixedwidth, div.menu_titre .fa, div.menu_titre .fas, div.menu_titre .far, div.menu_titre img {
	font-size: 16px !important;
	width: 20px !important;
	min-width: 20px;
	text-align: center;
	padding: 0 !important;
	margin: 0 !important;
	color: var(--primary) !important;
	opacity: 1 !important;
}
a.vmenu span, span.vmenu, span.vmenu span { color: inherit !important; }

/* Level 1+ */
div.menu_contenu {
	display: flex;
	align-items: center;
	padding: 0 !important;
	margin: 1px 0;
	white-space: nowrap;
	overflow: hidden !important;
	font-size: 0; /* hides the &nbsp; indentation text node, font size restored on children */
}
div.menu_contenu > * { font-size: 13.5px; }
div.menu_contenu br { display: none; }
div.menu_contenu a.vsmenu, div.menu_contenu span.vsmenu, div.menu_contenu span.vsmenudisabled, div.menu_contenu font.vsmenudisabled {
	flex: 1 1 auto;
	min-width: 0;
	display: block !important;
	margin: 0 !important;
	padding: 7px 12px 7px 42px !important;
	border-radius: var(--radius-control);
	font-size: 13.5px !important;
	font-weight: 400 !important;
	color: var(--text-secondary) !important;
	overflow: hidden;
	text-overflow: ellipsis;
	white-space: nowrap;
	text-decoration: none !important;
	transition: var(--transition);
	line-height: 20px;
	position: relative;
}
div.menu_contenu a.vsmenu:hover { background: var(--bg-hover); color: var(--text-primary) !important; }
div.menu_contenu span.vsmenudisabled, li a.vsmenudisabled { color: var(--text-muted) !important; }
/* Deeper levels (Dolibarr indents with &nbsp;) — marked by JS */
div.menu_contenu.mui-level2 a.vsmenu, div.menu_contenu.mui-level2 span.vsmenu { padding-left: 56px !important; font-size: 13px !important; }
div.menu_contenu.mui-level3 a.vsmenu, div.menu_contenu.mui-level3 span.vsmenu { padding-left: 68px !important; font-size: 13px !important; }
/* Active entry */
div.menu_contenu.mui-active a.vsmenu, div.menu_titre.mui-active a.vmenu {
	background: var(--primary-soft) !important;
	color: var(--primary) !important;
	font-weight: 500 !important;
}
div.menu_contenu.mui-active a.vsmenu::before {
	content: "";
	position: absolute;
	left: 0;
	top: 6px;
	bottom: 6px;
	width: 3px;
	border-radius: 0 3px 3px 0;
	background: var(--primary);
}
/* Warning pictos in menus become small dots */
.vmenu .pictowarning, .vmenu .fa-exclamation-triangle, #id-left .pictowarning {
	font-size: 0 !important;
	width: 8px !important;
	height: 8px !important;
	min-width: 8px !important;
	border-radius: 50%;
	background: var(--warning);
	display: inline-block !important;
	padding: 0 !important;
	margin: 0 0 0 6px !important;
	vertical-align: middle;
	box-shadow: 0 0 0 3px var(--warning-soft);
}
.vmenu .pictowarning::before, .vmenu .fa-exclamation-triangle::before { content: none !important; }
div.blockvmenuhelp { display: none; }
a.help:link, a.help:visited { color: var(--text-muted); }
<?php } // end !hideleft ?>

/* --------------------------------------------------------------------------
   5. Layout — Main content area
   -------------------------------------------------------------------------- */
<?php if (!$hideleft && !$hidetop) { ?>
/* native layout is display:table / table-cell: wide tables would stretch the page instead of scrolling */
#id-container { display: block !important; width: 100%; }
#id-right {
	display: block !important;
	width: auto !important;
	min-width: 0;
	padding-left: var(--sidebar-w) !important;
	padding-top: 0 !important;
	box-sizing: border-box;
}
	<?php if ($ismd) { // md: "sidebar-collapse" = user closed the menu. eldy always has this class on body (used for small screens only) ?>
@media only screen and (min-width: 992px) {
	body.sidebar-collapse #id-right { padding-left: 0 !important; }
	body.sidebar-collapse .side-nav { display: none !important; }
}
	<?php } ?>
<?php } ?>
div.fiche {
	margin: 24px 32px 32px !important;
}
div.fichecenter { clear: both; }
div.fichehalfleft, div.fichehalfright, div.fichethirdleft, div.fichetwothirdright { box-sizing: border-box; }
@media only screen and (min-width: 901px) {
	div.fichehalfleft, div.fichethirdleft { width: calc(50% - 12px) !important; }
	div.fichehalfright, div.fichetwothirdright { width: calc(50% - 12px) !important; }
}

/* --------------------------------------------------------------------------
   6. Page header & titles
   -------------------------------------------------------------------------- */
table.table-fiche-title { margin-bottom: 16px !important; border-collapse: collapse; }
table.table-fiche-title td { vertical-align: middle; }
.titre, div.titre, .titre a, div.titre a {
	font-family: var(--font) !important;
	font-size: 15px !important;
	font-weight: 600 !important;
	color: var(--text-primary) !important;
	text-transform: none !important;
	letter-spacing: -.005em;
	text-shadow: none !important;
}
.subtitle { color: var(--text-muted); font-size: 13px; }
td.nobordernopadding.widthpictotitle.col-picto { color: var(--primary) !important; opacity: 1 !important; padding-right: 4px !important; }
.pictotitle { margin-right: 10px !important; color: var(--primary) !important; opacity: 1 !important; }
/* Page title (first title of the page) — class added by JS */
table.table-fiche-title.mui-page-title { margin-bottom: 20px !important; }
table.mui-page-title .titre, table.mui-page-title div.titre, table.mui-page-title .titre a {
	font-size: 22px !important;
	font-weight: 600 !important;
	letter-spacing: -.02em;
	line-height: 1.3;
}
table.mui-page-title td.col-picto .pictotitle, table.mui-page-title td.col-picto span.pictotitle, table.mui-page-title td.col-picto img.pictotitle {
	display: inline-flex !important;
	align-items: center;
	justify-content: center;
	box-sizing: border-box;
	width: 40px !important;
	height: 40px !important;
	border-radius: 10px;
	background: var(--primary-soft);
	color: var(--primary) !important;
	font-size: 18px !important;
	line-height: 40px;
	padding: 0;
	margin-right: 14px !important;
}
table.mui-page-title td.col-picto img.pictotitle { padding: 9px !important; object-fit: contain; }
table.mui-page-title .totalnboflines {
	display: inline-block;
	font-size: 12px !important;
	font-weight: 600;
	color: var(--primary) !important;
	background: var(--primary-soft);
	border-radius: var(--radius-full);
	padding: 1px 10px;
	margin-left: 10px;
	opacity: 1 !important;
	vertical-align: middle;
}
table.table-fiche-title .col-title .opacitymedium.colorblack { color: var(--text-muted) !important; }

/* Title action buttons (new, view mode, ...) → segmented control */
html body :is(a.btnTitle, span.btnTitle, .btnTitle):not(.hideobject):not(.hidden) { display: inline-flex; }
a.btnTitle, span.btnTitle, .btnTitle {
	align-items: center;
	justify-content: center;
	gap: 6px;
	min-width: 36px;
	height: 36px !important;
	padding: 0 10px !important;
	margin: 0 0 0 -1px !important;
	box-sizing: border-box;
	background: var(--bg-surface) !important;
	border: 1px solid var(--border) !important;
	border-radius: 0 !important;
	color: var(--text-secondary) !important;
	box-shadow: none !important;
	text-decoration: none !important;
	transition: var(--transition);
	vertical-align: middle;
	position: relative;
}
.btnTitle .btnTitle-icon, .btnTitle .fa, .btnTitle .fas, .btnTitle span.fa { font-size: 14px !important; color: inherit !important; line-height: 1 !important; }
.btnTitle .btnTitle-label { display: none; }
a.btnTitle:hover { background: var(--bg-hover) !important; color: var(--text-primary) !important; z-index: 1; }
a.btnTitle.btnTitleSelected, .btnTitle.btnTitleSelected {
	background: var(--primary-soft) !important;
	color: var(--primary) !important;
	border-color: #C7D2FE !important;
	z-index: 2;
}
.mui-seg-first { border-top-left-radius: var(--radius-control) !important; border-bottom-left-radius: var(--radius-control) !important; }
.mui-seg-last { border-top-right-radius: var(--radius-control) !important; border-bottom-right-radius: var(--radius-control) !important; }
.mui-seg-single { border-radius: var(--radius-control) !important; }
/* The "+" create button is a primary button */
a.btnTitle.btnTitlePlus, .btnTitle.btnTitlePlus {
	background: var(--primary) !important;
	border-color: var(--primary) !important;
	color: #fff !important;
	border-radius: var(--radius-control) !important;
	margin-left: 8px !important;
	padding: 0 12px !important;
}
a.btnTitle.btnTitlePlus:hover { background: var(--primary-hover) !important; }
a.btnTitle.btnTitlePlus .btnTitle-label { display: inline; font-size: 13px; font-weight: 500; }
.btnTitle.refused, a.btnTitle.refused { opacity: .5; cursor: not-allowed; }
.button-title-separator { display: inline-block; width: 8px; }

/* --------------------------------------------------------------------------
   7. Tabs
   -------------------------------------------------------------------------- */
div.tabs {
	margin-top: 8px !important;
	border-bottom: 1px solid var(--border);
	display: flex;
	flex-wrap: wrap;
	align-items: flex-end;
	gap: 4px;
	height: auto !important;
}
div.tabs div.tabsElem { margin: 0 !important; }
div.tabs div.tabsElem.floatright { order: 99; margin-left: auto !important; align-self: center; }
div.tabsElem div.tab, div.tab.tabactive, div.tab.tabunactive { border: 0 !important; background: transparent !important; margin: 0 !important; }
html body :is(a.tab:link, a.tab:visited, a.tab#active, a.tab):not(.hideobject) { display: inline-flex; }
a.tab:link, a.tab:visited, a.tab:hover, a.tab#active, a.tab {
	align-items: center;
	gap: 6px;
	padding: 10px 14px !important;
	margin: 0 !important;
	margin-bottom: -1px !important;
	border: 0 !important;
	border-bottom: 2px solid transparent !important;
	border-radius: 0 !important;
	background: transparent !important;
	color: var(--text-secondary) !important;
	font-size: 13.5px;
	font-weight: 500 !important;
	text-decoration: none !important;
	transition: var(--transition);
}
a.tab:hover { color: var(--text-primary) !important; border-bottom-color: var(--border-strong) !important; }
.tabactive, a.tab#active, a.tab.tabactive, div.tabactive a.tab {
	color: var(--primary) !important;
	font-weight: 600 !important;
	background: transparent !important;
	border: 0 !important;
	border-bottom: 2px solid var(--primary) !important;
}
div.tabs .badge, a.tab .badge {
	background: var(--primary-soft) !important;
	color: var(--primary) !important;
	font-size: 12px !important;
	font-weight: 600 !important;
	border-radius: var(--radius-full) !important;
	padding: 0 8px !important;
	line-height: 18px !important;
	min-width: 0;
}
div.tabunactive a.tab .badge { background: var(--bg-hover) !important; color: var(--text-secondary) !important; }
div.popuptabset { border-radius: var(--radius-card); border: 1px solid var(--border); box-shadow: var(--shadow-lg); background: var(--bg-surface); }

div.tabBar, div.tabBar.tabBarWithBottom {
	margin-top: 16px !important;
	margin-bottom: 24px !important;
	padding: 24px !important;
	background: var(--bg-surface) !important;
	border: 1px solid var(--border) !important;
	border-radius: var(--radius-card) !important;
	box-shadow: var(--shadow-sm);
	color: var(--text-primary);
}
div.tabBar.tabBarNoTop { margin-top: 0 !important; }
div.underbanner, .underbanner { border-bottom: 1px solid var(--border) !important; }
div.arearef { border-bottom: 1px solid var(--border); padding-bottom: 12px; margin-bottom: 16px; }

/* Object banner (ref + status) */
div.refidno { color: var(--text-secondary); font-size: 13px; }
div.refid, .refid { font-size: 20px; font-weight: 600; color: var(--text-primary); letter-spacing: -.01em; }
img.photoref, div.photoref {
	border: 1px solid var(--border) !important;
	box-shadow: none !important;
	border-radius: var(--radius-card);
	background: var(--bg-subtle);
}
div.photoref .fa, div.photoref .fas, div.photoref .far { color: var(--primary); }
div.statusref { margin-top: 4px; }

/* --------------------------------------------------------------------------
   8. Buttons
   -------------------------------------------------------------------------- */
/* display is NOT !important: Dolibarr hides buttons with style="display:none", .hideobject or jQuery */
html body :is(.button, input.button, button.button, a.button, .butAction, a.butAction, span.butAction, .butActionNew, a.butActionNew,
.butActionDelete, a.butActionDelete, .butActionRefused, span.butActionRefused, .butActionNewRefused, .ui-button, button.ui-button):not(.hideobject):not(.hidden) {
	display: inline-flex;
}
.button, input.button, button.button, a.button, .butAction, a.butAction, span.butAction, .butActionNew, a.butActionNew,
.butActionDelete, a.butActionDelete, .butActionRefused, span.butActionRefused, .butActionNewRefused, .ui-button, button.ui-button {
	font-family: var(--font) !important;
	align-items: center;
	justify-content: center;
	gap: 6px;
	box-sizing: border-box;
	min-height: var(--control-h);
	height: auto;
	padding: 0 16px !important;
	margin: 4px 4px !important;
	border-radius: var(--radius-control) !important;
	font-size: 14px !important;
	font-weight: 500 !important;
	line-height: 1.2 !important;
	text-transform: none !important;
	text-shadow: none !important;
	letter-spacing: 0 !important;
	text-decoration: none !important;
	white-space: nowrap;
	vertical-align: middle;
	cursor: pointer;
	transition: var(--transition);
	background-image: none !important;
}
/* Primary */
.button, input.button, button.button, a.button, .butAction, a.butAction, span.butAction, .butActionNew, a.butActionNew,
input.button.button-save, .button-save {
	background: var(--primary) !important;
	border: 1px solid var(--primary) !important;
	color: #fff !important;
	box-shadow: var(--shadow-sm) !important;
}
.button:hover, input.button:hover, button.button:hover, a.button:hover, .butAction:hover, a.butAction:hover, .butActionNew:hover {
	background: var(--primary-hover) !important;
	border-color: var(--primary-hover) !important;
	color: #fff !important;
	box-shadow: var(--shadow-md) !important;
}
/* Secondary */
input.button.button-cancel, .button-cancel, button.button-cancel, input.buttonreset, .buttonreset, .button.buttongen.button-cancel,
.ui-button, button.ui-button, .ui-dialog-buttonpane button, input.button.smallpaddingimp.button-cancel {
	background: var(--bg-surface) !important;
	border: 1px solid var(--border) !important;
	color: var(--text-primary) !important;
	box-shadow: var(--shadow-sm) !important;
}
input.button.button-cancel:hover, .button-cancel:hover, .buttonreset:hover, .ui-button:hover, .ui-dialog-buttonpane button:hover {
	background: var(--bg-hover) !important;
	border-color: var(--border-strong) !important;
	color: var(--text-primary) !important;
}
/* md theme styles buttons with "#mainbody input.button:not(...)" (id specificity): mirror it */
#mainbody input.button:not(.buttongen):not(.bordertransp), #mainbody a.button:not(.buttongen):not(.bordertransp) {
	background: var(--primary);
	border-radius: var(--radius-control) !important;
}
#mainbody input.button:not(.buttongen):not(.bordertransp):hover, #mainbody a.button:not(.buttongen):not(.bordertransp):hover { box-shadow: var(--shadow-md) !important; }
#mainbody input.button.button-cancel:not(.buttongen):not(.bordertransp), #mainbody a.button.button-cancel:not(.buttongen):not(.bordertransp),
#mainbody input.buttonreset:not(.buttongen):not(.bordertransp), #mainbody .button.buttonreset:not(.buttongen):not(.bordertransp),
#mainbody div.divfilteralone input.button:not(.buttongen):not(.bordertransp), #mainbody .ui-dialog-buttonpane button {
	background: var(--bg-surface) !important;
	border: 1px solid var(--border) !important;
	color: var(--text-primary) !important;
	box-shadow: var(--shadow-sm) !important;
}
#mainbody input.button.button-cancel:hover, #mainbody div.divfilteralone input.button:hover, #mainbody input.buttonreset:hover { background: var(--bg-hover) !important; }
#mainbody input.button[disabled], #mainbody input.button:disabled { background: var(--bg-hover) !important; color: var(--text-muted) !important; border: 1px solid var(--border) !important; box-shadow: none !important; }
/* Danger */
.butActionDelete, a.butActionDelete, .button.button-delete, input.button-delete {
	background: var(--bg-surface) !important;
	border: 1px solid #FECACA !important;
	color: var(--danger) !important;
	box-shadow: var(--shadow-sm) !important;
}
.butActionDelete:hover, a.butActionDelete:hover { background: var(--danger) !important; border-color: var(--danger) !important; color: #fff !important; }
/* Disabled */
.butActionRefused, span.butActionRefused, a.butActionRefused, .butActionNewRefused, .button:disabled, .button.disabled, input.button[disabled], .butAction.disabled {
	background: var(--bg-hover) !important;
	border: 1px solid var(--border) !important;
	color: var(--text-muted) !important;
	opacity: .7 !important;
	cursor: not-allowed !important;
	box-shadow: none !important;
}
.button.smallpaddingimp, input.button.smallpaddingimp, .butAction.smallpaddingimp, .button.small { min-height: 32px; padding: 0 12px !important; font-size: 13px !important; }
div.tabsAction { margin: 24px 0 !important; display: flex; flex-wrap: wrap; justify-content: flex-end; gap: 8px; }
div.tabsAction > a, div.tabsAction > span, div.tabsAction .butAction, div.tabsAction .butActionDelete, div.tabsAction .butActionRefused { margin: 0 !important; }
div.tabsAction .dropdown-menu, .butAction.dropdown-toggle + .dropdown-menu { text-align: left; }

/* Icon buttons in list filters (search / reset) */
html body :is(button.button_search, button.button_removefilter):not(.hideobject) { display: inline-flex; }
button.button_search, button.button_removefilter, .button_search, .button_removefilter,
button.liste_titre.button_search, button.liste_titre.button_removefilter {
	align-items: center;
	justify-content: center;
	width: 34px;
	height: 34px;
	padding: 0 !important;
	margin: 0 2px !important;
	border-radius: var(--radius-control) !important;
	border: 1px solid var(--border) !important;
	background: var(--bg-surface) !important;
	color: var(--text-secondary) !important;
	box-shadow: none !important;
	cursor: pointer;
	transition: var(--transition);
}
button.button_search.button_search, button.liste_titre.button_search { background: var(--primary) !important; border-color: var(--primary) !important; color: #fff !important; }
button.button_search.button_search .fa, button.button_search.button_search span, button.liste_titre.button_search .fa { color: #fff !important; opacity: 1 !important; }
button.button_search.button_search:hover, button.liste_titre.button_search:hover { background: var(--primary-hover) !important; }
button.button_removefilter .fa, button.button_removefilter span { color: var(--text-secondary) !important; opacity: 1 !important; }
button.button_removefilter:hover { background: var(--bg-hover) !important; color: var(--text-primary) !important; }

/* --------------------------------------------------------------------------
   9. Form controls (input, select, textarea, select2)
   -------------------------------------------------------------------------- */
input[type="text"], input[type="password"], input[type="number"], input[type="email"], input[type="url"], input[type="search"],
input[type="tel"], input[type="date"], input[type="datetime-local"], input[type="time"], input[type="month"],
input.flat:not([type="checkbox"]):not([type="radio"]):not([type="submit"]):not([type="button"]):not([type="file"]):not([type="image"]),
select, select.flat, form.flat select, textarea, textarea.flat, .dataTables_length label select {
	font-family: var(--font) !important;
	font-size: 14px;
	color: var(--text-primary);
	background-color: var(--bg-surface);
	border: 1px solid var(--border) !important;
	border-radius: var(--radius-control) !important;
	box-sizing: border-box;
	transition: border-color .18s ease, box-shadow .18s ease, background-color .18s ease;
	outline: none;
	box-shadow: none;
}
input[type="text"], input[type="password"], input[type="number"], input[type="email"], input[type="url"], input[type="search"],
input[type="tel"], input[type="date"], input[type="datetime-local"], input[type="time"], input[type="month"],
input.flat:not([type="checkbox"]):not([type="radio"]):not([type="submit"]):not([type="button"]):not([type="file"]):not([type="image"]) {
	height: var(--control-h) !important;
	padding: 0 12px !important;
	line-height: normal !important;
}
input.maxwidthdate, input.maxwidthdateonsmartphone, input[id$="day"][type="text"], input.datepicker, input.hasDatepicker {
	min-width: 118px;
	padding: 0 8px !important;
}
select, select.flat, form.flat select { height: var(--control-h); padding: 0 32px 0 12px !important; }
select[multiple], select[size]:not([size="1"]) { height: auto; padding: 6px 8px !important; }
/* Day / month / year pickers: core forces maxwidth75imp/width75, which truncates values once our select padding applies */
select.flat[name$="day"], select.flat[name$="month"], select.flat[name$="year"] {
	width: auto !important; max-width: none !important; min-width: 72px; padding: 0 30px 0 10px !important; margin-right: 6px;
}
textarea, textarea.flat { padding: 10px 12px !important; line-height: 1.5; min-height: 38px; }
input:hover:not(:focus):not([type="checkbox"]):not([type="radio"]):not(.button):not([type="submit"]), select:hover:not(:focus), textarea:hover:not(:focus) { border-color: var(--border-strong) !important; }

input:focus:not([type="checkbox"]):not([type="radio"]):not(.button):not([type="submit"]):not(.select2-search__field),
select:focus, textarea:focus, textarea:focus:not(.ia-input, .cke_source) {
	border: 1px solid var(--primary) !important;
	border-radius: var(--radius-control) !important;
	box-shadow: 0 0 0 3px var(--primary-ring) !important;
	outline: none !important;
}
input[disabled], select[disabled], textarea[disabled], input[readonly].flat { background: var(--bg-hover) !important; color: var(--text-muted) !important; }
input[type="checkbox"], input[type="radio"] { accent-color: var(--primary); width: 16px; height: 16px; vertical-align: middle; }
input[type="file"] { font-size: 13px; }
input[type="file"]::file-selector-button {
	font-family: var(--font);
	border: 1px solid var(--border);
	background: var(--bg-surface);
	border-radius: var(--radius-control);
	padding: 6px 12px;
	margin-right: 10px;
	cursor: pointer;
}
.fieldrequired { font-weight: 500 !important; color: var(--text-primary) !important; }
.fieldrequired::after { content: " *"; color: var(--danger); font-weight: 600; }

/* Select2 */
.select2-container { font-family: var(--font); }
.select2-container .select2-selection--single, .select2-container--default .select2-selection--single {
	height: var(--control-h) !important;
	border: 1px solid var(--border) !important;
	border-radius: var(--radius-control) !important;
	background: var(--bg-surface) !important;
	box-shadow: none !important;
}
.select2-container--default .select2-selection--single .select2-selection__rendered {
	line-height: 36px !important;
	padding-left: 12px !important;
	padding-right: 28px !important;
	color: var(--text-primary);
	font-size: 14px;
}
.select2-container--default .select2-selection--single .select2-selection__placeholder { color: var(--text-muted) !important; }
.select2-container--default .select2-selection--single .select2-selection__arrow { height: 36px !important; right: 6px !important; }
.select2-container .select2-selection--multiple, .select2-container--default .select2-selection--multiple {
	min-height: var(--control-h) !important;
	border: 1px solid var(--border) !important;
	border-radius: var(--radius-control) !important;
	background: var(--bg-surface) !important;
	padding: 2px 4px !important;
}
.select2-container--default.select2-container--focus .select2-selection--multiple,
.select2-container--default.select2-container--open .select2-selection--single,
.select2-container--default.select2-container--open .select2-selection--multiple,
.select2-container--focus span.selection span.select2-selection:not(.noborderfocus):not(.massactionselect),
.select2-container--open [aria-expanded="false"].select2-selection--single {
	border: 1px solid var(--primary) !important;
	border-radius: var(--radius-control) !important;
	box-shadow: 0 0 0 3px var(--primary-ring) !important;
}
.select2-container--default .select2-selection--multiple .select2-selection__choice {
	background: var(--primary-soft) !important;
	border: 0 !important;
	color: var(--primary) !important;
	border-radius: 6px !important;
	padding: 1px 8px !important;
	font-size: 12.5px;
	font-weight: 500;
}
/* Inline search field of multiple select2 must not inherit the input box style */
.select2-container .select2-search--inline .select2-search__field, .select2-selection--multiple input.select2-search__field {
	height: 28px !important;
	min-height: 0 !important;
	margin: 3px 0 0 4px !important;
	padding: 0 4px !important;
	border: 0 !important;
	border-radius: 0 !important;
	box-shadow: none !important;
	background: transparent !important;
	font-size: 13.5px;
}
.select2-container--default .select2-selection--multiple .select2-selection__rendered { padding: 0 2px !important; }
.select2-container--default .select2-selection--multiple .select2-selection__choice { margin-top: 5px !important; }
.select2-dropdown, .select2-container--open .select2-dropdown--below, .select2-container--open .select2-dropdown--above {
	border: 1px solid var(--border) !important;
	border-radius: 10px !important;
	box-shadow: var(--shadow-lg) !important;
	overflow: hidden;
	background: var(--bg-surface);
	margin-top: 4px;
}
.select2-search--dropdown { padding: 8px !important; }
.select2-search--dropdown .select2-search__field, .select2-container--default .select2-search--dropdown .select2-search__field {
	height: 34px;
	border: 1px solid var(--border) !important;
	border-radius: var(--radius-control) !important;
	padding: 0 10px !important;
}
.select2-results__option { padding: 8px 12px !important; font-size: 13.5px; }
.select2-container--default .select2-results__option--highlighted[aria-selected], .select2-container--default .select2-results__option--highlighted,
.select2-container--default .select2-results__option--highlighted.select2-results__option--selectable {
	background: var(--primary-soft) !important;
	color: var(--primary) !important;
}
.select2-container--default .select2-results__option[aria-selected="true"], .select2-results__option--selected { background: var(--bg-hover) !important; color: var(--text-primary); }

/* --------------------------------------------------------------------------
   10. Filter / search toolbar
   -------------------------------------------------------------------------- */
div.liste_titre.liste_titre_bydiv, div.divsearchfieldfilter, div.liste_titre_bydiv {
	background: var(--bg-surface) !important;
	border: 1px solid var(--border) !important;
	border-radius: var(--radius-card) !important;
	box-shadow: var(--shadow-sm);
	padding: 12px 16px !important;
	margin-bottom: 16px !important;
	display: flex;
	flex-wrap: wrap;
	align-items: center;
	gap: 10px;
	box-sizing: border-box;
	width: 100% !important;
}
div.liste_titre_bydiv .divsearchfield .fa, div.liste_titre_bydiv .divsearchfield .fas { color: var(--text-muted) !important; opacity: 1 !important; }
div.divsearchfield { margin: 0 !important; padding: 0 !important; display: inline-flex; align-items: center; gap: 6px; }
.liste_titre input.flat, tr.liste_titre_filter input.flat, tr.liste_titre_filter select, tr.liste_titre_filter .select2-selection--single {
	height: 34px !important;
	font-size: 13px;
}
tr.liste_titre_filter .select2-container--default .select2-selection--single .select2-selection__rendered { line-height: 32px !important; }
tr.liste_titre_filter .select2-container--default .select2-selection--single .select2-selection__arrow { height: 32px !important; }
/* Standalone filter bar (Setup > Modules and similar pages) */
div.divfilteralone, div.divfilteralone.colorbacktimesheet {
	display: inline-flex !important;
	flex-wrap: wrap;
	align-items: center;
	gap: 8px;
	background: var(--bg-subtle) !important;
	border: 1px solid var(--border);
	border-radius: var(--radius-card);
	padding: 8px 10px !important;
	margin: 0 0 8px 0;
	float: none !important;
}
div.divfilteralone .divsearchfield { padding: 0 !important; }
div.divfilteralone .fa-filter { color: var(--text-muted) !important; opacity: 1 !important; padding: 0 4px 0 4px !important; }
div.divfilteralone input[type="text"] { min-width: 180px; background: var(--bg-surface); }
div.divfilteralone input.button, div.divfilteralone input.button.small {
	background: var(--bg-surface) !important;
	border: 1px solid var(--border) !important;
	color: var(--text-primary) !important;
	box-shadow: none !important;
	margin: 0 !important;
}
div.divfilteralone input.button:hover { background: var(--bg-hover) !important; }
div.divfilteralone .select2-container { min-width: 160px; }
div.pagination.--module-list { padding-top: 8px; }
div.tabBar > br:first-of-type { display: none; }

/* --------------------------------------------------------------------------
   11. Tables & lists
   -------------------------------------------------------------------------- */
div.div-table-responsive, div.div-table-responsive-no-min {
	background: var(--bg-surface);
	border: 1px solid var(--border) !important;
	border-radius: var(--radius-card);
	box-shadow: var(--shadow-sm);
	overflow-x: auto;
	margin-bottom: 20px;
}
div.div-table-responsive > table, div.div-table-responsive-no-min > table,
div.div-table-responsive table.liste, div.div-table-responsive-no-min table.noborder {
	border: 0 !important;
	border-radius: 0 !important;
	box-shadow: none !important;
	margin: 0 !important;
}
table.liste, table.noborder:not(.nobordernopadding):not(.table-fiche-title):not(.paymenttable):not(.boxtablenotop),
.tagtable.liste, table.boxtable {
	width: 100%;
	border-collapse: separate !important;
	border-spacing: 0;
	background: var(--bg-surface);
	border: 1px solid var(--border) !important;
	border-radius: var(--radius-card);
	box-shadow: var(--shadow-sm);
	/* no overflow:hidden here: it would clip wide tables (e.g. payments block of an invoice) */
}
/* Rounded corners without clipping: round the corner cells instead */
table.noborder > tbody > tr:first-child > :first-child, table.liste > tbody > tr:first-child > :first-child,
table.noborder > thead > tr:first-child > :first-child, table.liste > thead > tr:first-child > :first-child { border-top-left-radius: var(--radius-card); }
table.noborder > tbody > tr:first-child > :last-child, table.liste > tbody > tr:first-child > :last-child,
table.noborder > thead > tr:first-child > :last-child, table.liste > thead > tr:first-child > :last-child { border-top-right-radius: var(--radius-card); }
table.noborder > tbody > tr:last-child > :first-child, table.liste > tbody > tr:last-child > :first-child { border-bottom-left-radius: var(--radius-card); }
table.noborder > tbody > tr:last-child > :last-child, table.liste > tbody > tr:last-child > :last-child { border-bottom-right-radius: var(--radius-card); }
td table.noborder, .tabBar table.noborder, td table.liste, div.box table.noborder, form table.noborder table.noborder {
	box-shadow: none !important;
}
.tabBar table.noborder:not(.boxtable) { border-radius: 10px; }
table.liste th, table.liste td, table.noborder th, table.noborder td, .tagtable.liste .tagtd, .tagtable.liste .tagth {
	border-left: 0 !important;
	border-right: 0 !important;
}
/* Header */
tr.liste_titre th, tr.liste_titre td, th.liste_titre, td.liste_titre, tr.liste_titre_sel th, tr.liste_titre_sel td,
table.liste th, table.noborder th, .tagtable .liste_titre .tagtd, div.liste_titre:not(.liste_titre_bydiv) {
	background: var(--bg-subtle) !important;
	color: var(--text-secondary) !important;
	font-size: 12px !important;
	font-weight: 600 !important;
	text-transform: uppercase;
	letter-spacing: .04em;
	padding: 12px 12px !important;
	border-top: 0 !important;
	border-bottom: 1px solid var(--border) !important;
	font-family: var(--font) !important;
	white-space: nowrap;
}
tr.liste_titre th a, tr.liste_titre td a, th.liste_titre a, tr.liste_titre_sel th a { color: var(--text-secondary) !important; font-weight: 600 !important; }
/* Form controls placed inside a header row (old-style filters, e.g. Accounting > turnover reports) keep normal typography */
tr.liste_titre .select2-container, tr.liste_titre select, tr.liste_titre input, tr.liste_titre label, tr.liste_titre .select2-selection__rendered,
tr.liste_titre .select2-selection__placeholder, tr.liste_titre_filter .select2-container {
	text-transform: none !important;
	letter-spacing: 0 !important;
	font-weight: 400 !important;
	font-size: 13.5px !important;
}
/* Header cell used as a filter area → toolbar layout, one line per Dolibarr <br> */
tr.liste_titre > td:has(.select2-container, select), tr.liste_titre > th:has(.select2-container, select) {
	background: var(--bg-surface) !important;
	text-transform: none !important;
	letter-spacing: 0 !important;
	font-weight: 400 !important;
	color: var(--text-secondary) !important;
	white-space: normal !important;
	padding: 14px 16px !important;
	vertical-align: middle;
	line-height: 1;
}
/* Filter content is wrapped by JS into .mui-filterbar (the <td> itself must stay a table cell) */
.mui-filterbar { display: flex; flex-wrap: wrap; align-items: center; gap: 10px 8px; }
.mui-filterbar > .mui-break { flex-basis: 100%; height: 0; margin: 0; }
tr.mui-moved { display: none !important; }
.mui-filtercard {
	display: flex;
	align-items: flex-start;
	gap: 16px;
	background: var(--bg-surface);
	border: 1px solid var(--border);
	border-radius: var(--radius-card);
	box-shadow: var(--shadow-sm);
	padding: 14px 16px;
	margin: 0 0 16px;
}
.mui-filtercard .mui-filterbar { flex: 1 1 auto; min-width: 0; }
.mui-filtercard .mui-filterbar .select2-container { width: 240px !important; max-width: 100%; }
.mui-filtercard-actions { flex: 0 0 auto; display: flex; align-items: center; gap: 8px; }
.mui-filtercard-actions input[type="image"] {
	width: 38px;
	height: 38px;
	padding: 10px;
	box-sizing: border-box;
	border: 1px solid var(--border);
	border-radius: var(--radius-control);
	background: var(--bg-surface);
	box-shadow: var(--shadow-sm);
	cursor: pointer;
	transition: var(--transition);
}
.mui-filtercard-actions input[type="image"]:hover { background: var(--primary-soft); border-color: #C7D2FE; }
@media only screen and (max-width: 767px) {
	.mui-filtercard { flex-direction: column; }
	.mui-filtercard .mui-filterbar .select2-container { width: 100% !important; }
}
.mui-filterbar > .pictofixedwidth {
	width: 32px !important;
	height: 32px;
	display: inline-flex !important;
	align-items: center;
	justify-content: center;
	border-radius: 8px;
	background: var(--bg-hover);
	color: var(--text-muted) !important;
	opacity: 1 !important;
	margin: 0 !important;
	padding: 0 !important;
	flex: 0 0 32px;
}
.mui-filterbar .select2-container { min-width: 220px; margin: 0 !important; }
.mui-filterbar label { color: var(--text-secondary) !important; font-size: 13px !important; margin: 0 12px 0 -2px !important; cursor: pointer; }
.mui-filterbar input[type="checkbox"] { margin: 0 0 0 6px !important; }
/* Old image search button (search.png) → icon button */
tr.liste_titre input[type="image"][name="button_search"], input.liste_titre[type="image"] {
	width: 36px;
	height: 36px;
	padding: 9px;
	box-sizing: border-box;
	border: 1px solid var(--border);
	border-radius: var(--radius-control);
	background: var(--bg-surface);
	cursor: pointer;
	transition: var(--transition);
	vertical-align: middle;
}
tr.liste_titre input[type="image"][name="button_search"]:hover { background: var(--primary-soft); border-color: #C7D2FE; }
tr.liste_titre_sel th, th.liste_titre_sel, tr.liste_titre th.liste_titre_sel, th.liste_titre_sel a { color: var(--primary) !important; }
tr.liste_titre .fa, th .imgup, th .imgdown, th img.imgup, th img.imgdown { opacity: .8; }
/* Filter row */
tr.liste_titre_filter td, tr.liste_titre_filter th, tr.liste_titre_filter {
	background: var(--bg-surface) !important;
	padding: 8px 8px !important;
	border-bottom: 1px solid var(--border) !important;
	text-transform: none;
	letter-spacing: 0;
}
/* Body rows */
tr.oddeven > td, tr.pair > td, tr.impair > td, .tagtable .oddeven > .tagtd, table.liste > tbody > tr > td, table.noborder > tbody > tr:not(.liste_titre):not(.liste_titre_filter) > td {
	background: var(--bg-surface);
	color: var(--text-primary);
	border-top: 0 !important;
	border-bottom: 1px solid var(--border) !important;
	padding: 10px 12px !important;
	height: 28px;
}
tr.oddeven, tr.pair, tr.impair { background: var(--bg-surface) !important; }
tr.oddeven:hover td, tr.pair:hover td, tr.impair:hover td, .tagtable .oddeven:hover .tagtd, tr.oddeven:hover { background: var(--bg-subtle) !important; }
tr.highlight td, tr.oddeven.highlight td, tr.oddeven:has(input.checkforselect:checked) td { background: var(--primary-soft) !important; }
table.liste tr:last-of-type td, table.noborder tr:last-of-type td, .tagtable .tagtr:last-child .tagtd { border-bottom: 0 !important; }
tr.liste_total td, tr.liste_total_wrap td, td.liste_total, tr.trforbreak td {
	background: var(--bg-subtle) !important;
	font-weight: 600;
	color: var(--text-primary);
	border-top: 1px solid var(--border) !important;
	padding: 12px !important;
}
table.noborder > tbody > tr:first-child:not(.liste_titre) > td { border-top: 0 !important; }
td.nobordernopadding, table.nobordernopadding td,
table table.nobordernopadding > tbody > tr > td, table tr > td.nobordernopadding { border: 0 !important; padding: 0 !important; height: auto; background: transparent !important; }

/* Pagination */
div.pagination ul, ul.pagination { display: inline-flex; align-items: center; gap: 4px; padding: 0; margin: 0; }
div.pagination li, ul.pagination li, li.pagination { list-style: none; float: none !important; margin: 0 !important; padding: 0 !important; }
html body :is(div.pagination li, ul.pagination li, li.pagination):not(.hideobject):not(.hideonsmartphone) { display: inline-flex; }
html body :is(div.pagination li.pagination a, div.pagination li.pagination span, li.pagination a, li.pagination span:not(.fa):not(.fas)):not(.hideobject) { display: inline-flex; }
div.pagination li.pagination a, div.pagination li.pagination span, li.pagination a, li.pagination span:not(.fa):not(.fas) {
	align-items: center;
	justify-content: center;
	min-width: 32px;
	height: 32px;
	padding: 0 8px !important;
	box-sizing: border-box;
	border-radius: var(--radius-control) !important;
	border: 1px solid var(--border) !important;
	background: var(--bg-surface) !important;
	color: var(--text-secondary) !important;
	font-size: 13px;
	font-weight: 500;
	text-decoration: none !important;
	transition: var(--transition);
}
div.pagination li.pagination a:hover, li.pagination a:hover { background: var(--bg-hover) !important; color: var(--text-primary) !important; }
div.pagination li.pagination span.active, li.pagination span.active, div.pagination li .active {
	background: var(--primary) !important;
	border-color: var(--primary) !important;
	color: #fff !important;
}
div.pagination li.pagination span.inactive { border: 0 !important; background: transparent !important; }
div.pagination li.paginationafterarrows, div.pagination li.paginationbeforearrows { margin-left: 4px !important; }
div.pagination select, select.selectlimit { height: 32px !important; font-size: 13px; }

/* Form / card tables */
table.border, table.border.tableforfield, table.tableforfield, .tableforfieldcreate, table.border.centpercent {
	border-collapse: collapse !important;
	border: 0 !important;
	background: transparent;
}
table.border > tbody > tr > td, table.tableforfield > tbody > tr > td, table.border td, table.tableforfield td {
	border: 0 !important;
	border-bottom: 1px solid var(--border) !important;
	padding: 10px 12px !important;
	vertical-align: middle;
	background: transparent;
}
table.border > tbody > tr:last-child > td, table.tableforfield > tbody > tr:last-child > td { border-bottom: 0 !important; }
td.titlefield, td.titlefieldcreate, td.titlefieldmiddle, td.titlefieldmax45, table.border td.titlefield, .tableforfield td.titlefield, table.border td:first-child.fieldrequired,
.tableforfield td:first-child, table.border.tableforfield td:first-child {
	font-size: 13px !important;
	font-weight: 500 !important;
	color: var(--text-secondary) !important;
}
table.border tr.liste_titre td, table.border tr.liste_titre th { border-bottom: 1px solid var(--border) !important; }
.tableforfield td.titlefield a, .tableforfield td:first-child a { color: var(--text-secondary); }

/* Kanban & boxes (dashboard widgets) */
div.box, div.divboxtable { margin-bottom: 20px !important; }
div.box table.boxtable, table.noborder.boxtable {
	border: 1px solid var(--border) !important;
	border-radius: var(--radius-card) !important;
	box-shadow: var(--shadow-sm);
	overflow: hidden;
}
tr.box_titre td, tr.liste_titre.box_titre td, tr.box_titre th, tr.liste_titre.box_titre th, td.box_titre {
	background: var(--bg-surface) !important;
	color: var(--text-primary) !important;
	font-size: 14px !important;
	font-weight: 600 !important;
	text-transform: none !important;
	letter-spacing: 0 !important;
	padding: 14px 16px !important;
	border-bottom: 1px solid var(--border) !important;
}
tr.box_titre a, tr.liste_titre.box_titre a { color: var(--text-primary) !important; }
tr.box_titre .fa, tr.box_titre .fas, .boxhandle .fa, .boxclose .fa { color: var(--text-muted) !important; }
tr.box_titre .badge { background: var(--primary-soft) !important; color: var(--primary) !important; }

/* --------------------------------------------------------------------------
   12. Badges & statuses
   -------------------------------------------------------------------------- */
.badge, span.badge {
	display: inline-flex;
	align-items: center;
	gap: 4px;
	border-radius: var(--radius-full) !important;
	font-size: 12px !important;
	font-weight: 500 !important;
	padding: 2px 10px !important;
	line-height: 18px !important;
	font-family: var(--font) !important;
	text-shadow: none !important;
	border: 0 !important;
	background: var(--primary-soft);
	color: var(--primary);
	white-space: nowrap;
}
.badge.marginleftonlyshort, .badge.marginleftonly { padding: 0 8px !important; font-weight: 600 !important; }
.badge-status0, .badge-status5, .badge-status6 { background: #F1F5F9 !important; color: #475569 !important; border: 0 !important; }
.badge-status1, .badge-status1b { background: #EFF6FF !important; color: #1D4ED8 !important; border: 0 !important; }
.badge-status2 { background: #EEF2FF !important; color: #4338CA !important; border: 0 !important; }
.badge-status3 { background: #FFFBEB !important; color: #B45309 !important; border: 0 !important; }
.badge-status4, .badge-status4b, .badge-status7, .badge-status11 { background: #ECFDF5 !important; color: #047857 !important; border: 0 !important; }
.badge-status8, .badge-status9, .badge-status10 { background: #FEF2F2 !important; color: #B91C1C !important; border: 0 !important; }
.badge-status::before, span.badge-status::before {
	content: "";
	width: 6px;
	height: 6px;
	border-radius: 50%;
	background: currentColor;
	opacity: .8;
	display: inline-block;
}
.badge.badge-dot::before { content: none; }
.badge-secondary { background: #F1F5F9 !important; color: #475569 !important; }
.badge-success { background: #ECFDF5 !important; color: #047857 !important; }
.badge-warning { background: #FFFBEB !important; color: #B45309 !important; }
.badge-danger { background: #FEF2F2 !important; color: #B91C1C !important; }
.badge-info { background: #EFF6FF !important; color: #1D4ED8 !important; }
.badge-primary { background: var(--primary-soft) !important; color: var(--primary) !important; }
.font-status4, .fa-toggle-on.font-status4 { color: var(--success) !important; }
.font-status8 { color: var(--danger) !important; }

/* --------------------------------------------------------------------------
   13. Toggle switch (replaces fa-toggle-on / fa-toggle-off everywhere)
   -------------------------------------------------------------------------- */
html body :is(span.fa-toggle-on, span.fa-toggle-off, .fa.fa-toggle-on, .fa.fa-toggle-off):not(.hideobject):not(.hidden) { display: inline-block; }
span.fa-toggle-on, span.fa-toggle-off, .fa.fa-toggle-on, .fa.fa-toggle-off, .fas.fa-toggle-on, .fas.fa-toggle-off {
	position: relative;
	width: 40px !important;
	height: 22px !important;
	min-width: 40px;
	padding: 0 !important;
	margin: 0 4px !important;
	border-radius: var(--radius-full);
	font-size: 0 !important;
	line-height: 22px !important;
	vertical-align: middle;
	background: var(--border-strong);
	transition: background-color .2s ease;
	cursor: pointer;
	opacity: 1 !important;
	transform: none !important;
}
span.fa-toggle-on, .fa.fa-toggle-on, .fas.fa-toggle-on { background: var(--success); }
span.fa-toggle-on::before, span.fa-toggle-off::before, .fa-toggle-on::before, .fa-toggle-off::before {
	content: "" !important;
	position: absolute;
	top: 3px;
	left: 3px;
	width: 16px;
	height: 16px;
	border-radius: 50%;
	background: #fff;
	box-shadow: 0 1px 3px rgba(15, 23, 42, .25);
	transition: left .2s ease;
}
span.fa-toggle-on::before, .fa-toggle-on::before { left: 21px; }
a:hover > span.fa-toggle-off { background: var(--text-muted); }
a:hover > span.fa-toggle-on { background: #059669; }
span.fa-toggle-on.opacitymedium, span.fa-toggle-off.opacitymedium, span.fa-toggle-off.disabled, .fa-toggle-on.disabled { opacity: .5 !important; cursor: not-allowed; }

/* --------------------------------------------------------------------------
   14. Messages, notifications, dialogs, dropdowns, tooltips
   -------------------------------------------------------------------------- */
div.error, div.warning, div.info, div.ok, div.neutral, div.informationbox {
	border-radius: 10px !important;
	border: 1px solid var(--border) !important;
	border-left-width: 4px !important;
	padding: 12px 16px !important;
	margin: 12px 0 !important;
	font-size: 13.5px;
	line-height: 1.55;
	box-shadow: none !important;
	background-image: none !important;
	color: var(--text-primary) !important;
}
div.error { background: var(--danger-soft) !important; border-color: #FECACA !important; border-left-color: var(--danger) !important; color: #991B1B !important; }
div.warning { background: var(--warning-soft) !important; border-color: #FDE68A !important; border-left-color: var(--warning) !important; color: #92400E !important; }
div.info, div.informationbox { background: var(--info-soft) !important; border-color: #BFDBFE !important; border-left-color: var(--info) !important; color: #1E3A8A !important; }
div.ok { background: var(--success-soft) !important; border-color: #A7F3D0 !important; border-left-color: var(--success) !important; color: #065F46 !important; }
div.error a, div.warning a, div.info a { color: inherit !important; text-decoration: underline !important; }

/* jNotify toasts */
.jnotify-container {
	top: calc(var(--topbar-h) + 12px) !important;
	right: 20px !important;
	left: auto !important;
	width: 380px !important;
	max-width: calc(100vw - 32px);
	margin: 0 !important;
}
.jnotify-container .jnotify-notification {
	position: relative;
	margin-bottom: 10px !important;
	border-radius: 12px !important;
	overflow: hidden;
	box-shadow: var(--shadow-lg) !important;
	border: 1px solid var(--border) !important;
	border-left: 4px solid var(--success) !important;
	background: var(--bg-surface) !important;
}
.jnotify-container .jnotify-notification .jnotify-background {
	background: var(--bg-surface) !important;
	opacity: 1 !important;
	border-radius: 0 !important;
	box-shadow: none !important;
}
.jnotify-container .jnotify-notification .jnotify-message {
	color: var(--text-primary) !important;
	font-family: var(--font) !important;
	font-size: 13.5px !important;
	line-height: 1.5 !important;
	padding: 14px 36px 14px 44px !important;
	text-shadow: none !important;
}
.jnotify-container .jnotify-notification .jnotify-message::before {
	content: "\f058";
	font-family: "Font Awesome 5 Free";
	font-weight: 900;
	position: absolute;
	left: 16px;
	top: 14px;
	font-size: 16px;
	color: var(--success);
}
.jnotify-container .jnotify-notification-warning { border-left-color: var(--warning) !important; }
.jnotify-container .jnotify-notification-warning .jnotify-message::before { content: "\f071"; color: var(--warning); }
.jnotify-container .jnotify-notification-error { border-left-color: var(--danger) !important; }
.jnotify-container .jnotify-notification-error .jnotify-message::before { content: "\f06a"; color: var(--danger); }
.jnotify-container .jnotify-notification-warning .jnotify-background, .jnotify-container .jnotify-notification-error .jnotify-background { background: var(--bg-surface) !important; }
.jnotify-container .jnotify-notification a.jnotify-close {
	color: var(--text-muted) !important;
	top: 10px !important;
	right: 12px !important;
	font-size: 18px !important;
	text-shadow: none !important;
}

/* jQuery UI dialogs */
.ui-widget-overlay, .ui-widget-overlay.ui-front { background: rgba(15, 23, 42, .45) !important; opacity: 1 !important; backdrop-filter: blur(2px); z-index: 1030 !important; }
.ui-dialog.ui-front { z-index: 1031 !important; }
.ui-dialog ~ .select2-container--open, body > .select2-container--open { z-index: 1040; }
.ui-dialog, .ui-dialog.ui-widget, .ui-dialog.ui-widget-content {
	border: 0 !important;
	border-radius: var(--radius-modal) !important;
	box-shadow: var(--shadow-lg) !important;
	padding: 0 !important;
	overflow: hidden;
	background: var(--bg-surface) !important;
	font-family: var(--font) !important;
}
.ui-dialog .ui-dialog-titlebar, .ui-dialog .ui-widget-header {
	background: var(--bg-surface) !important;
	border: 0 !important;
	border-bottom: 1px solid var(--border) !important;
	border-radius: 0 !important;
	padding: 16px 20px !important;
	color: var(--text-primary) !important;
}
.ui-dialog .ui-dialog-title { font-size: 16px !important; font-weight: 600 !important; color: var(--text-primary) !important; }
.ui-dialog .ui-dialog-titlebar-close {
	width: 32px !important;
	height: 32px !important;
	margin-top: -16px !important;
	right: 12px !important;
	border: 0 !important;
	border-radius: var(--radius-control) !important;
	background: transparent !important;
	box-shadow: none !important;
	padding: 0 !important;
}
.ui-dialog .ui-dialog-titlebar-close:hover { background: var(--bg-hover) !important; }
.ui-dialog .ui-dialog-content { padding: 20px !important; color: var(--text-primary); font-size: 14px; }
.ui-dialog .ui-dialog-buttonpane {
	border-top: 1px solid var(--border) !important;
	background: var(--bg-subtle) !important;
	padding: 12px 16px !important;
	margin: 0 !important;
}
.ui-dialog .ui-dialog-buttonpane .ui-dialog-buttonset button:last-child,
#mainbody .ui-dialog .ui-dialog-buttonpane .ui-dialog-buttonset button:last-child {
	background: var(--primary) !important;
	border-color: var(--primary) !important;
	color: #fff !important;
}
.ui-dialog .ui-dialog-buttonpane .ui-dialog-buttonset button:last-child:hover,
#mainbody .ui-dialog .ui-dialog-buttonpane .ui-dialog-buttonset button:last-child:hover { background: var(--primary-hover) !important; }

/* Dropdowns (user menu, bookmarks, search, actions) */
.dropdown-menu, ul.dropdown-menu, div.dropdown-menu, .ui-menu, .ui-autocomplete {
	border: 1px solid var(--border) !important;
	border-radius: 10px !important;
	box-shadow: var(--shadow-lg) !important;
	background: var(--bg-surface) !important;
	padding: 6px !important;
	font-family: var(--font) !important;
	overflow: hidden;
}
.dropdown-menu .dropdown-item, .dropdown-menu > li > a, .dropdown-menu a.dropdown-item, .ui-menu .ui-menu-item-wrapper, .ui-autocomplete .ui-menu-item a {
	display: block;
	padding: 8px 12px !important;
	border-radius: 6px;
	color: var(--text-primary) !important;
	font-size: 13.5px;
	text-decoration: none !important;
	transition: var(--transition);
}
.dropdown-menu .dropdown-item:hover, .dropdown-menu > li > a:hover, .ui-menu .ui-state-active, .ui-menu .ui-menu-item-wrapper.ui-state-active, .ui-autocomplete .ui-state-focus {
	background: var(--bg-hover) !important;
	color: var(--text-primary) !important;
	border: 0 !important;
	margin: 0 !important;
}
#topmenu-login-dropdown .dropdown-menu, .login_block .dropdown-menu { margin-top: 8px !important; width: 300px; padding: 0 !important; }
.dropdown-menu > .user-header, .side-nav-vert .user-menu .dropdown-menu > .user-header, .dropdown-menu .user-header {
	background: linear-gradient(135deg, var(--primary), var(--accent)) !important;
	color: #fff !important;
	border-radius: 0 !important;
	padding: 20px 16px !important;
	min-height: 0 !important;
}
.dropdown-menu > .user-header *, .dropdown-menu .user-header a, .dropdown-menu .user-header span { color: #fff !important; }
.dropdown-menu .user-body, .dropdown-menu > .user-body { padding: 12px 16px !important; color: var(--text-secondary) !important; border: 0 !important; font-size: 13px; }
.dropdown-menu .user-footer, .dropdown-menu > .user-footer { background: var(--bg-subtle) !important; padding: 12px 16px !important; border-top: 1px solid var(--border) !important; }
.dropdown-menu .user-footer .button-top-menu-dropdown, .dropdown-menu .user-footer a.button-top-menu-dropdown {
	background: var(--bg-surface) !important;
	border: 1px solid var(--border) !important;
	border-radius: var(--radius-control) !important;
	color: var(--text-primary) !important;
	padding: 6px 12px !important;
	font-size: 13px;
	box-shadow: none !important;
}
.dropdown-menu .user-footer .button-top-menu-dropdown:hover { background: var(--bg-hover) !important; }
.dropdown-menu .dropdown-header, .dropdown-menu .bookmark-header { background: var(--bg-subtle) !important; color: var(--text-secondary) !important; border-bottom: 1px solid var(--border) !important; }
.dropdown-menu .bookmark-footer, .dropdown-menu .dropdown-footer { border-top: 1px solid var(--border) !important; background: var(--bg-subtle) !important; }
.dropdown dd ul, dl.dropdown dd ul { border: 1px solid var(--border) !important; border-radius: 10px !important; box-shadow: var(--shadow-lg) !important; background: var(--bg-surface) !important; padding: 6px !important; }

/* Tooltips */
/* Dolibarr tooltips are rich "object cards" (title, status badge, photo, label: value lines) → light card */
.ui-tooltip, .ui-tooltip.mytooltip, div.ui-tooltip.ui-widget, div.ui-tooltip.ui-widget-content {
	background: var(--bg-surface) !important;
	color: var(--text-primary) !important;
	border: 1px solid var(--border) !important;
	border-radius: 12px !important;
	box-shadow: var(--shadow-lg) !important;
	padding: 12px 16px !important;
	font-family: var(--font) !important;
	font-size: 13px !important;
	line-height: 1.65 !important;
	max-width: 440px;
	opacity: 1 !important;
}
.ui-tooltip .ui-tooltip-content { color: var(--text-primary); }
/* Title line: "<u>Bank account</u> <badge>" */
.ui-tooltip u {
	text-decoration: none !important;
	font-weight: 600;
	font-size: 14px;
	color: var(--text-primary);
	margin-right: 4px;
}
.ui-tooltip u:first-child ~ br:first-of-type { display: block; content: ""; margin-bottom: 6px; }
/* Labels "Ref:" are printed in <b> */
.ui-tooltip b, .ui-tooltip strong { font-weight: 500; color: var(--text-secondary); }
.ui-tooltip a { color: var(--primary) !important; }
.ui-tooltip .opacitymedium { color: var(--text-muted) !important; }
.ui-tooltip hr { border: 0; border-top: 1px solid var(--border); margin: 8px 0; }
.ui-tooltip .fa, .ui-tooltip .fas, .ui-tooltip .far { color: var(--primary); opacity: 1; }
.ui-tooltip .badge { vertical-align: middle; margin-left: 2px; }
.ui-tooltip img, .ui-tooltip .userphoto { filter: none; }
.ui-tooltip img.photoref, .ui-tooltip .photoref { border-radius: 10px; }
.classfortooltip .fa-info-circle, span.fa-info-circle, .fa-question-circle { color: var(--text-muted) !important; }

/* Date picker */
.ui-datepicker { border: 1px solid var(--border) !important; border-radius: var(--radius-card) !important; box-shadow: var(--shadow-lg) !important; padding: 8px !important; font-family: var(--font) !important; background: var(--bg-surface) !important; }
.ui-datepicker .ui-datepicker-header { background: transparent !important; border: 0 !important; color: var(--text-primary) !important; font-weight: 600; }
.ui-datepicker td a, .ui-datepicker td span { border-radius: 6px !important; border: 0 !important; background: transparent !important; text-align: center !important; }
.ui-datepicker td a:hover { background: var(--bg-hover) !important; }
.ui-datepicker .ui-state-highlight, .ui-datepicker .ui-state-active { background: var(--primary) !important; color: #fff !important; }

/* --------------------------------------------------------------------------
   15. Module cards (Setup > Modules/Applications), info-box, dashboard
   -------------------------------------------------------------------------- */
/* Family title: "Quản lý nhân sự" + count + hairline */
table.modulefamilygroup, table.table-fiche-title.modulefamilygroup {
	display: block !important;
	margin: 32px 0 16px !important;
	border: 0 !important;
	box-shadow: none !important;
	background: transparent !important;
	cursor: pointer;
	width: 100% !important;
}
table.modulefamilygroup.mui-first { margin-top: 12px !important; }
table.modulefamilygroup tbody, table.modulefamilygroup tr { display: flex !important; width: 100%; align-items: center; }
table.modulefamilygroup td.col-title { display: flex !important; align-items: center; flex: 1 1 auto; gap: 10px; }
table.modulefamilygroup td.col-title::after { content: ""; flex: 1 1 auto; height: 1px; background: var(--border); margin-left: 6px; }
table.modulefamilygroup .titre, table.modulefamilygroup div.titre {
	font-size: 15px !important;
	font-weight: 600 !important;
	color: var(--text-primary) !important;
	text-transform: none !important;
	display: inline-flex !important;
	align-items: center;
	gap: 8px;
}
table.modulefamilygroup .modulefamilytoggleicon, table.modulefamilygroup .fa-folder, table.modulefamilygroup .fa-folder-open { display: none !important; }
table.modulefamilygroup .mui-chevron { font-size: 11px; color: var(--text-muted); transition: transform .18s ease; width: 12px; }
table.modulefamilygroup.mui-collapsed .mui-chevron { transform: rotate(-90deg); }
.mui-count {
	display: inline-flex;
	align-items: center;
	height: 20px;
	padding: 0 8px;
	border-radius: var(--radius-full);
	background: var(--bg-hover);
	color: var(--text-secondary);
	font-size: 12px;
	font-weight: 600;
}

/* Grid */
div.box-flex-container.kanban, .box-flex-container.kanban {
	display: grid !important;
	grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
	gap: 16px;
	margin: 0 !important;
	width: 100% !important;
}
.box-flex-container.kanban > .box-flex-item.filler { display: none !important; }
.box-flex-container.kanban > .box-flex-item, .box-flex-item.info-box-module, .info-box-module {
	width: auto !important;
	min-width: 0 !important;
	max-width: none !important;
	margin: 0 !important;
	display: flex;
}
/* Card */
.info-box-module .info-box, .info-box.info-box-sm.info-box-module, .box-flex-item.info-box-module > .info-box {
	display: flex !important;
	flex-direction: column;
	width: 100%;
	min-height: 176px !important;
	height: 100%;
	margin: 0 !important;
	padding: 20px !important;
	box-sizing: border-box;
	background: var(--bg-surface) !important;
	border: 1px solid var(--border) !important;
	border-radius: var(--radius-card) !important;
	box-shadow: var(--shadow-sm) !important;
	transition: transform .18s ease, box-shadow .18s ease, border-color .18s ease;
	position: relative;
}
.box-flex-item.info-box-module > .info-box { flex: 1 1 auto; width: auto !important; max-width: none !important; min-width: 0 !important; }
.info-box-module .info-box:hover {
	transform: translateY(-2px);
	box-shadow: var(--shadow-md) !important;
	border-color: #C7D2FE !important;
}
.info-box-module .info-box-icon, .info-box-sm.info-box-module .info-box-icon, .info-box-module .info-box-icon.info-box-icon-module-enabled {
	float: none !important;
	display: flex !important;
	align-items: center;
	justify-content: center;
	width: 40px !important;
	height: 40px !important;
	min-height: 0 !important;
	line-height: 40px !important;
	padding: 0 !important;
	margin: 0 0 14px 0 !important;
	border-radius: 10px !important;
	font-size: 18px !important;
	overflow: visible !important;
	background: var(--fam-other-bg) !important;
	color: var(--fam-other) !important;
	position: static;
	filter: none !important;
}
.info-box-module .info-box-icon > img, .info-box-module .info-box-icon img {
	max-width: 22px !important;
	max-height: 22px !important;
	width: auto !important;
	height: auto !important;
	margin: 0 !important;
	filter: none;
}
.info-box-module .info-box-icon .fa, .info-box-module .info-box-icon .fas, .info-box-module .info-box-icon .far, .info-box-module .info-box-icon .fab,
.info-box-module .info-box-icon span[class*="fa-"], .info-box-module .info-box-icon .infobox-adherent, .info-box-module .info-box-icon [class*="pictofixedwidth"] {
	color: inherit !important;
	font-size: 18px !important;
	opacity: 1 !important;
	width: auto !important;
	padding: 0 !important;
	margin: 0 !important;
}
/* Family colors (container tagged by JS) */
[data-mui-family="hr"] .info-box-module .info-box-icon.info-box-icon { background: var(--fam-hr-bg) !important; color: var(--fam-hr) !important; }
[data-mui-family="crm"] .info-box-module .info-box-icon.info-box-icon { background: var(--fam-crm-bg) !important; color: var(--fam-crm) !important; }
[data-mui-family="fin"] .info-box-module .info-box-icon.info-box-icon { background: var(--fam-fin-bg) !important; color: var(--fam-fin) !important; }
[data-mui-family="prod"] .info-box-module .info-box-icon.info-box-icon { background: var(--fam-prod-bg) !important; color: var(--fam-prod) !important; }
[data-mui-family="proj"] .info-box-module .info-box-icon.info-box-icon { background: var(--fam-proj-bg) !important; color: var(--fam-proj) !important; }
[data-mui-family="other"] .info-box-module .info-box-icon.info-box-icon { background: var(--fam-other-bg) !important; color: var(--fam-other) !important; }
/* Module list mode (table) also gets family-colored pictos */
[data-mui-family="hr"] td.tdsetuppicto, [data-mui-family="hr"] .tdsetuppicto .fa, [data-mui-family="hr"] td:first-child > .fa, [data-mui-family="hr"] td:first-child > .fas { color: var(--fam-hr) !important; }
[data-mui-family="crm"] td:first-child > .fa, [data-mui-family="crm"] td:first-child > .fas { color: var(--fam-crm) !important; }
[data-mui-family="fin"] td:first-child > .fa, [data-mui-family="fin"] td:first-child > .fas { color: var(--fam-fin) !important; }
[data-mui-family="prod"] td:first-child > .fa, [data-mui-family="prod"] td:first-child > .fas { color: var(--fam-prod) !important; }
[data-mui-family="proj"] td:first-child > .fa, [data-mui-family="proj"] td:first-child > .fas { color: var(--fam-proj) !important; }
.info-box-module .info-box-icon img { filter: saturate(1.1); }

/* Version badge (external / experimental) */
.info-box-module .info-box-icon-version, .info-box-sm .info-box-icon-version {
	position: absolute !important;
	top: 28px;
	left: 70px;
	bottom: auto !important;
	width: auto !important;
	padding: 1px 8px !important;
	border-radius: var(--radius-full);
	background: var(--bg-hover) !important;
	color: var(--text-secondary) !important;
	font-size: 11px !important;
	font-weight: 500;
	line-height: 18px !important;
	opacity: 1 !important;
	overflow: visible !important;
}
.info-box-module .info-box-icon-version.warning { background: var(--warning-soft) !important; color: #B45309 !important; }

.info-box-module .info-box-content, .info-box-sm.info-box-module .info-box-content, .info-box-module .info-box-content.info-box-module-enabled {
	margin: 0 !important;
	padding: 0 !important;
	height: auto !important;
	flex: 1 1 auto;
	display: flex;
	flex-direction: column;
	background: transparent !important;
	border-radius: 0 !important;
}
.info-box-module .info-box-content > br { display: none; }
.info-box-module .info-box-title {
	font-size: 14px !important;
	font-weight: 600 !important;
	color: var(--text-primary) !important;
	text-transform: none !important;
	white-space: normal !important;
	line-height: 1.4;
	margin: 0 0 4px 0 !important;
	width: 100% !important;
	padding-right: 8px;
	box-sizing: border-box;
}
.info-box-module .info-box-desc, .info-box-module .info-box-desc.opacitymedium {
	font-size: 13px !important;
	color: var(--text-secondary) !important;
	opacity: 1 !important;
	line-height: 1.5;
	display: -webkit-box !important;
	-webkit-line-clamp: 2;
	-webkit-box-orient: vertical;
	overflow: hidden;
	white-space: normal !important;
	margin-bottom: 16px;
}
/* (i) info link — top right, shown on hover */
.info-box-module .info-box-more {
	position: absolute !important;
	top: 16px !important;
	right: 16px !important;
	float: none !important;
	opacity: .35;
	transition: opacity .18s ease;
}
.info-box-module .info-box:hover .info-box-more { opacity: 1; }
.info-box-module .info-box-more img, .info-box-module .info-box-more .fa, .info-box-module .info-box-more span { width: 16px; height: 16px; color: var(--text-muted) !important; font-size: 14px !important; }
/* Footer: status on the left, setup + toggle on the right */
.info-box-module .info-box-actions {
	position: static !important;
	display: flex !important;
	align-items: center;
	justify-content: flex-end;
	gap: 6px;
	margin-top: auto;
	padding-top: 12px;
	border-top: 1px solid var(--border);
	width: 100%;
}
.info-box-module .info-box-actions > div { margin: 0 !important; display: inline-flex !important; align-items: center; }
.info-box-module .info-box-actions::before {
	content: "Enabled";
	margin-right: auto;
	display: inline-flex;
	align-items: center;
	gap: 6px;
	font-size: 12.5px;
	font-weight: 500;
	color: #047857;
}
html:lang(vi) .info-box-module .info-box-actions::before { content: "Đang bật"; }
.box-flex-item.info-box-module.--disabled .info-box-actions::before { content: "Disabled"; color: var(--text-muted); }
html:lang(vi) .box-flex-item.info-box-module.--disabled .info-box-actions::before { content: "Đã tắt"; }
.info-box-module .info-box-actions { --mui-dot: var(--success); }
.box-flex-item.info-box-module.--disabled .info-box-actions { --mui-dot: var(--border-strong); }
.info-box-module .info-box-actions::after {
	content: "";
	order: -1;
	width: 8px;
	height: 8px;
	border-radius: 50%;
	background: var(--mui-dot);
	margin-right: -2px;
}
.info-box-module .info-box-actions::before { order: 0; }
.info-box-module .info-box-actions .info-box-setup { order: 1; }
.info-box-module .info-box-actions > div:last-child { order: 2; }
.info-box-module .info-box-setup a, .info-box-module .info-box-setup span.fa, .info-box-module .info-box-setup > span {
	display: inline-flex;
	align-items: center;
	justify-content: center;
	width: 32px;
	height: 32px;
	border-radius: var(--radius-control);
	color: var(--text-secondary) !important;
	transition: var(--transition);
}
.info-box-module .info-box-setup a:hover { background: var(--primary-soft); color: var(--primary) !important; }
.info-box-module .info-box-setup .fa, .info-box-module .info-box-setup .fas, .info-box-module .info-box-setup img { font-size: 15px !important; color: inherit !important; opacity: 1 !important; }
.info-box-module .info-box-setup .opacitytransp, .info-box-module .info-box-setup .opacitymedium { opacity: .35 !important; }
/* Disabled module card */
.box-flex-item.info-box-module.--disabled .info-box { opacity: .72; }
.box-flex-item.info-box-module.--disabled .info-box:hover { opacity: 1; }
.box-flex-item.info-box-module.--disabled .info-box-module .info-box-icon.info-box-icon { background: #F1F5F9 !important; color: var(--text-muted) !important; filter: grayscale(1) !important; }
.info-box-content-warning span.font-status4 { color: #B45309 !important; }

/* Generic info-box (dashboard "opened dashboard", other kanbans) */
.info-box:not(.info-box-module) {
	background: var(--bg-surface) !important;
	border: 1px solid var(--border) !important;
	border-radius: var(--radius-card) !important;
	box-shadow: var(--shadow-sm) !important;
	overflow: hidden;
	transition: box-shadow .18s ease, transform .18s ease;
}
.info-box:not(.info-box-module):hover { box-shadow: var(--shadow-md) !important; }
.info-box:not(.info-box-module) .info-box-icon {
	background: var(--primary-soft) !important;
	color: var(--primary) !important;
	border-radius: 0 !important;
}
.info-box:not(.info-box-module) .info-box-icon i, .info-box:not(.info-box-module) .info-box-icon .fa { color: inherit; }
.bg-infobox-project, .bg-infobox-action { background: var(--fam-proj-bg) !important; color: var(--fam-proj) !important; }
.bg-infobox-propal, .bg-infobox-facture, .bg-infobox-commande, .bg-infobox-contrat, .bg-infobox-ticket { background: var(--fam-crm-bg) !important; color: var(--fam-crm) !important; }
.bg-infobox-supplier_proposal, .bg-infobox-invoice_supplier, .bg-infobox-order_supplier, .bg-infobox-cubes { background: var(--fam-prod-bg) !important; color: var(--fam-prod) !important; }
.bg-infobox-bank_account { background: var(--fam-fin-bg) !important; color: var(--fam-fin) !important; }
.bg-infobox-adherent, .bg-infobox-member, .bg-infobox-expensereport, .bg-infobox-holiday { background: var(--fam-hr-bg) !important; color: var(--fam-hr) !important; }
.bg-infobox-project i.fa, .bg-infobox-action i.fa, .bg-infobox-propal i.fa, .bg-infobox-facture i.fa, .bg-infobox-commande i.fa, .bg-infobox-order_supplier i.fa,
.bg-infobox-contrat i.fa, .bg-infobox-ticket i.fa, .bg-infobox-bank_account i.fa, .bg-infobox-adherent i.fa, .bg-infobox-member i.fa, .bg-infobox-expensereport i.fa,
.bg-infobox-holiday i.fa, .bg-infobox-cubes i.fa, .bg-infobox-supplier_proposal i.fa, .bg-infobox-invoice_supplier i.fa { color: inherit !important; }
.info-box-title { text-transform: none !important; font-weight: 600 !important; color: var(--text-primary); font-size: 13.5px !important; }
.info-box-number { font-size: 26px !important; font-weight: 700 !important; letter-spacing: -.02em; color: var(--text-primary); }
.info-box-line, .info-box-text { font-size: 13px !important; color: var(--text-secondary); }
.opened-dash-board-wrap .box-flex-container { gap: 16px; margin: 0 !important; }
.opened-dash-board-wrap .box-flex-item { margin: 0 !important; }

/* KPI stats boxes (home page) */
.boxstatsindicator, a.boxstatsindicator { text-decoration: none !important; }
.boxstats, .boxstats130, .boxstats150, .boxstatsborder, div.boxstats {
	background: var(--bg-surface) !important;
	border: 1px solid var(--border) !important;
	border-radius: var(--radius-card) !important;
	box-shadow: var(--shadow-sm) !important;
	padding: 14px 16px !important;
	margin: 6px !important;
	box-sizing: border-box;
	text-align: left !important;
	transition: box-shadow .18s ease, transform .18s ease, border-color .18s ease;
	min-height: 88px;
	width: auto !important;
	min-width: 170px;
	max-width: none !important;
	height: auto !important;
}
/* Stats boxes on object cards (thirdparty...) are smaller than the home KPIs */
/* Containers of stats boxes (home widget "Database statistics", thirdparty card...) → responsive grid */
td.tdwidgetstate, td.tdboxstats, div.tabBar td.tdboxstats {
	display: grid !important;
	grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
	gap: 12px;
	padding: 16px !important;
	height: auto !important;
	box-sizing: border-box;
	text-align: left !important;
	background: var(--bg-surface) !important;
}
td.tdwidgetstate > br, td.tdboxstats > br { display: none; }
td.tdwidgetstate > a.boxstatsindicator, td.tdboxstats > a.boxstatsindicator, td.tdwidgetstate > .boxstatsempty, td.tdwidgetstate > .boxstats150empty {
	display: block !important;
	width: auto !important;
	min-width: 0 !important;
	margin: 0 !important;
}
td.tdwidgetstate > .boxstatsempty, td.tdwidgetstate > .boxstats150empty, td.tdboxstats > .boxstatsempty { display: none !important; }
td.tdwidgetstate .boxstats, td.tdboxstats .boxstats, td.tdboxstats .boxstats130 {
	display: flex !important;
	flex-direction: column;
	justify-content: space-between;
	gap: 8px;
	width: 100% !important;
	min-width: 0 !important;
	min-height: 0;
	height: 100% !important;
	margin: 0 !important;
	padding: 14px 16px !important;
	box-sizing: border-box;
}
td.tdwidgetstate .boxstats > br, td.tdboxstats .boxstats > br { display: none; }
td.tdwidgetstate .boxstatstext, td.tdboxstats .boxstatstext { font-size: 12.5px !important; white-space: nowrap; }
td.tdwidgetstate .boxstats span.boxstatsindicator, td.tdboxstats .boxstats span.boxstatsindicator {
	display: flex !important;
	align-items: center;
	gap: 10px;
	margin: 0 !important;
	font-size: 22px !important;
	line-height: 1.2;
	white-space: nowrap;
}
div.tabBar td.tdboxstats .boxstats span.boxstatsindicator { font-size: 18px !important; }
/* Icon in a soft rounded square */
td.tdwidgetstate span.boxstatsindicator > .fa, td.tdwidgetstate span.boxstatsindicator > .fas, td.tdwidgetstate span.boxstatsindicator > .far,
td.tdwidgetstate span.boxstatsindicator > img {
	flex: 0 0 34px;
	width: 34px !important;
	height: 34px;
	border-radius: 9px;
	display: inline-flex !important;
	align-items: center;
	justify-content: center;
	font-size: 15px !important;
	background: var(--primary-soft);
	color: var(--primary) !important;
	opacity: 1 !important;
	padding: 0 !important;
	margin: 0 !important;
	box-sizing: border-box;
}
td.tdwidgetstate span.boxstatsindicator > img { padding: 7px !important; object-fit: contain; }
/* Family colors by entity */
td.tdwidgetstate a[href*="/user/"] span.boxstatsindicator > .fas, td.tdwidgetstate a[href*="/adherents/"] span.boxstatsindicator > .fas,
td.tdwidgetstate a[href*="/holiday/"] span.boxstatsindicator > .fas, td.tdwidgetstate a[href*="/expensereport/"] span.boxstatsindicator > .fas { background: var(--fam-hr-bg); color: var(--fam-hr) !important; }
td.tdwidgetstate a[href*="/societe/"] span.boxstatsindicator > .fas, td.tdwidgetstate a[href*="/contact/"] span.boxstatsindicator > .fas,
td.tdwidgetstate a[href*="/comm/"] span.boxstatsindicator > .fas, td.tdwidgetstate a[href*="/commande/"] span.boxstatsindicator > .fas,
td.tdwidgetstate a[href*="/ticket/"] span.boxstatsindicator > .fas, td.tdwidgetstate a[href*="/contrat/"] span.boxstatsindicator > .fas,
td.tdwidgetstate a[href*="/fichinter/"] span.boxstatsindicator > .fas { background: var(--fam-crm-bg); color: var(--fam-crm) !important; }
td.tdwidgetstate a[href*="/compta/"] span.boxstatsindicator > .fas, td.tdwidgetstate a[href*="/don/"] span.boxstatsindicator > .fas,
td.tdwidgetstate a[href*="/bank/"] span.boxstatsindicator > .fas { background: var(--fam-fin-bg); color: var(--fam-fin) !important; }
td.tdwidgetstate a[href*="/product/"] span.boxstatsindicator > .fas, td.tdwidgetstate a[href*="/expedition/"] span.boxstatsindicator > .fas,
td.tdwidgetstate a[href*="/fourn/"] span.boxstatsindicator > .fas { background: var(--fam-prod-bg); color: var(--fam-prod) !important; }
td.tdwidgetstate a[href*="/projet/"] span.boxstatsindicator > .fas, td.tdwidgetstate a[href*="/recruitment/"] span.boxstatsindicator > .fas,
td.tdwidgetstate a[href*="/knowledgemanagement/"] span.boxstatsindicator > .fas, td.tdwidgetstate a[href*="/partnership/"] span.boxstatsindicator > .fas { background: var(--fam-proj-bg); color: var(--fam-proj) !important; }
.tabBar .boxstats .boxstatstext { white-space: normal; }
a.boxstatsindicator:hover .boxstats, .boxstats:hover { box-shadow: var(--shadow-md) !important; transform: translateY(-2px); border-color: #C7D2FE !important; }
.boxstatstext, span.boxstatstext {
	display: block;
	font-size: 12.5px !important;
	color: var(--text-muted) !important;
	font-weight: 500;
	opacity: 1 !important;
	white-space: nowrap;
	overflow: hidden;
	text-overflow: ellipsis;
	line-height: 1.4;
}
/* "boxstatsindicator" is used both on the wrapping <a> and on the number <span> */
a.boxstatsindicator { display: inline-block; vertical-align: top; font-size: inherit !important; margin: 0 !important; }
span.boxstatsindicator, .boxstats span.boxstatsindicator {
	display: block;
	font-size: 28px !important;
	font-weight: 700 !important;
	letter-spacing: -.02em;
	color: var(--text-primary) !important;
	line-height: 1.2;
	margin-top: 6px;
}
.boxstats .fa, .boxstats .fas, .boxstats .pictofixedwidth, .boxstats img { color: var(--primary) !important; opacity: 1 !important; }
.boxstatscontent { display: flex; align-items: center; gap: 6px; }
td.tdboxstats { background: transparent !important; border: 0 !important; padding: 0 !important; }
.tdboxstats.flexcontainer { gap: 4px; }
.boxstatsempty, .boxstats150empty, .boxstats130empty { margin: 6px !important; }

/* Graphs */
.dolgraphtitle { font-weight: 600; color: var(--text-primary); }

/* --------------------------------------------------------------------------
   16. Login page (body.bodylogin) — form on the left, gradient hero on the right
   -------------------------------------------------------------------------- */
body.bodylogin {
	background: var(--bg-surface) !important;
	background-image: none !important;
	display: flex !important;
	position: static !important;
	min-height: 100vh;
	height: auto !important;
	margin: 0;
}
body.bodylogin .login_center {
	display: flex !important;
	align-items: center;
	justify-content: center;
	flex: 0 0 50%;
	max-width: 50%;
	min-height: 100vh;
	padding: 32px;
	box-sizing: border-box;
	background: var(--bg-surface) !important; /* overrides the inline diagonal gradient */
	background-image: none !important;
}
body.bodylogin .login_vertical_align { padding: 0 !important; width: 100%; max-width: 420px; }
body.bodylogin form#login { padding: 0 !important; font-size: 14px !important; }
body.bodylogin .login_table_title, body.bodylogin a.login_table_title {
	display: none !important;
}
body.bodylogin .login_table {
	max-width: 420px !important;
	padding: 0 !important;
	margin: 0 !important;
	background: transparent !important;
	border: 0 !important;
	box-shadow: none !important;
	border-radius: 0 !important;
}
body.bodylogin #login_line1 { display: flex; flex-direction: column; align-items: stretch; }
body.bodylogin div#login_left, body.bodylogin div#login_right {
	display: block !important;
	min-width: 0 !important;
	padding: 0 !important;
	text-align: left !important;
}
body.bodylogin div#login_left { margin-bottom: 28px; }
body.bodylogin #img_logo, body.bodylogin .img_logo { max-height: 48px !important; max-width: 180px !important; border-radius: 8px; }
.mui-login-brand { display: inline-flex; align-items: center; gap: 12px; }
.mui-login-brand .mui-brand-mark { width: 44px; height: 44px; flex-basis: 44px; border-radius: 12px; font-size: 20px; }
.mui-login-brand-name { font-size: 22px; font-weight: 700; letter-spacing: -.02em; color: var(--text-primary); }
.mui-login-heading { margin: 0 0 28px; }
.mui-login-heading h1 { font-size: 26px; font-weight: 700; letter-spacing: -.02em; margin: 0 0 6px; color: var(--text-primary); line-height: 1.25; }
.mui-login-heading p { margin: 0; color: var(--text-secondary); font-size: 14px; }
body.bodylogin .trinputlogin, body.bodylogin .login_table .trinputlogin { display: block; margin: 0 0 14px !important; }
body.bodylogin .tdinputlogin, body.bodylogin .login_table .tdinputlogin {
	display: flex !important;
	align-items: center;
	position: relative;
	min-width: 0 !important;
	background: transparent !important;
	border-radius: 0;
	text-align: left !important;
}
body.bodylogin .tdinputlogin .fa, body.bodylogin .login_table .tdinputlogin .fa {
	position: absolute;
	left: 14px;
	top: 50%;
	transform: translateY(-50%);
	width: 16px !important;
	padding: 0 !important;
	color: var(--text-muted);
	z-index: 1;
	font-size: 14px;
}
body.bodylogin .login_table input#username, body.bodylogin .login_table input#password, body.bodylogin .login_table input#securitycode,
body.bodylogin input.input-icon-user, body.bodylogin input.input-icon-password {
	width: 100% !important;
	height: 46px !important;
	margin: 0 !important;
	padding: 0 44px 0 42px !important;
	border: 1px solid var(--border) !important;
	border-radius: 10px !important;
	font-size: 14.5px !important;
	background: var(--bg-surface) !important;
	box-sizing: border-box;
	transition: border-color .18s ease, box-shadow .18s ease;
}
body.bodylogin .login_table input#username:focus, body.bodylogin .login_table input#password:focus, body.bodylogin .login_table input#securitycode:focus {
	border-color: var(--primary) !important;
	box-shadow: 0 0 0 3px var(--primary-ring) !important;
	outline: none !important;
}
body.bodylogin .login_table #tdpasswordlogin #togglepassword { top: 50% !important; transform: translateY(-50%); right: 10px !important; opacity: .5 !important; }
body.bodylogin .login_table #tdpasswordlogin #togglepassword .fa { position: static; transform: none; }
body.bodylogin #login_line2 { margin-top: 8px; }
body.bodylogin #login-submit-wrapper { margin-top: 6px; }
body.bodylogin input.butAction.butActionLogin, body.bodylogin .butActionLogin {
	width: 100% !important;
	height: 46px !important;
	margin: 0 !important;
	border-radius: 10px !important;
	font-size: 15px !important;
	font-weight: 600 !important;
	background: var(--primary) !important;
	border: 0 !important;
	box-shadow: 0 6px 16px rgba(79, 70, 229, .28) !important;
}
body.bodylogin .butActionLogin:hover { background: var(--primary-hover) !important; }
body.bodylogin .alogin, body.bodylogin .alogin:hover, body.bodylogin a.alogin {
	color: var(--primary) !important;
	font-size: 13px !important;
	font-weight: 500 !important;
	text-decoration: none !important;
}
body.bodylogin .login_main_message { max-width: 420px; margin: 0 0 16px; text-align: left; }
body.bodylogin .login_main_message .error { border-radius: 10px; }
body.bodylogin table.login_table_securitycode { width: 100%; }
body.bodylogin #img_securitycode { border-radius: 8px; border: 1px solid var(--border) !important; }
body.bodylogin .login_main_home { display: none; }
body.bodylogin div#login_right select#entity { width: 100%; margin: 0 0 14px !important; height: 46px; border-radius: 10px !important; }

.mui-login-hero {
	flex: 1 1 50%;
	min-height: 100vh;
	position: relative;
	overflow: hidden;
	display: flex;
	flex-direction: column;
	justify-content: space-between;
	padding: 56px;
	box-sizing: border-box;
	color: #fff;
	background: radial-gradient(1200px 600px at 110% -10%, rgba(255, 255, 255, .22), transparent 60%),
		radial-gradient(800px 500px at -10% 120%, rgba(6, 182, 212, .55), transparent 60%),
		linear-gradient(135deg, var(--primary) 0%, #6366F1 45%, var(--accent) 100%);
}
.mui-login-hero::before, .mui-login-hero::after {
	content: "";
	position: absolute;
	border-radius: 50%;
	border: 1px solid rgba(255, 255, 255, .18);
	pointer-events: none;
}
.mui-login-hero::before { width: 520px; height: 520px; right: -160px; top: -120px; }
.mui-login-hero::after { width: 380px; height: 380px; left: -120px; bottom: -140px; }
.mui-hero-top { display: flex; align-items: center; gap: 12px; position: relative; z-index: 1; font-weight: 600; font-size: 16px; }
.mui-hero-top .mui-brand-mark { background: rgba(255, 255, 255, .2); box-shadow: none; backdrop-filter: blur(4px); }
.mui-hero-body { position: relative; z-index: 1; max-width: 520px; }
.mui-hero-body h2 { font-size: 40px; line-height: 1.15; font-weight: 700; letter-spacing: -.03em; margin: 0 0 16px; color: #fff; }
.mui-hero-body p { font-size: 16px; line-height: 1.6; margin: 0; color: rgba(255, 255, 255, .85); }
.mui-hero-points { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 12px; position: relative; z-index: 1; }
.mui-hero-point {
	background: rgba(255, 255, 255, .12);
	border: 1px solid rgba(255, 255, 255, .2);
	border-radius: var(--radius-card);
	padding: 14px 16px;
	backdrop-filter: blur(6px);
	font-size: 13px;
	line-height: 1.45;
	color: rgba(255, 255, 255, .92);
}
.mui-hero-point strong { display: block; font-size: 20px; font-weight: 700; color: #fff; margin-bottom: 2px; }

/* --------------------------------------------------------------------------
   17. Misc pages & helpers
   -------------------------------------------------------------------------- */
.fiche .arearef img.pictoedit, .fiche .fichecenter span.pictoedit, span.pictoedit, .fa-pencil-alt { color: var(--text-muted); }
.pictodelete, .fa-trash { color: var(--text-muted) !important; }
.fa-trash:hover { color: var(--danger) !important; }
.amountremaintopay, .amountremaintopayback { color: var(--danger) !important; }
.amountpaymentcomplete { color: var(--success) !important; }
.warning, span.warning, font.warning { color: #B45309; }
.error, span.error, font.error { color: var(--danger); }
.ok, span.ok { color: var(--success); }
.nowrap.minwidth100.valignmiddle.inline-block.floatleft { float: none; }
img.userphoto, span.userimg div.userphoto { border-radius: 50%; }
.kanbanlabel { background: var(--bg-subtle); border-radius: var(--radius-control); }
.ecmjqft, .jqueryFileTree { font-family: var(--font); }
.cke_chrome, .cke { border-radius: var(--radius-control) !important; border-color: var(--border) !important; }
.tox.tox-tinymce { border-radius: var(--radius-control) !important; border-color: var(--border) !important; }
div.blockvmenubookmarks .vmenusearchselectcombo { width: 100% !important; }
.divphotoref, .refidno { color: var(--text-secondary); }
.inline-block.valignmiddle.dateuser, .dateuser { color: var(--text-muted); }
.progress, div.progress { border-radius: var(--radius-full) !important; background: var(--bg-hover) !important; overflow: hidden; }
.progress-bar { background: var(--primary) !important; }
.titlefieldcreate, .titlefield { min-width: 180px; }
table.table-fiche-title .col-right { white-space: nowrap; }
/* Home: working board */
table.boxworkingboard td { padding: 10px 12px !important; }
/* Stats graphs container */
.dolgraph, div.dolgraph { border-radius: var(--radius-card); }
/* "Show more" / collapsible links */
a.reposition { text-decoration: none; }

/* --------------------------------------------------------------------------
   18. Responsive
   -------------------------------------------------------------------------- */
@media only screen and (max-width: 1799px) {
	/* icon only, label kept for the active entry — full label in the native tooltip */
	ul.tmenu li:not(.tmenusel) .mainmenuaspan, ul.tmenu li:not(.tmenusel) a.tmenulabel, ul.tmenu li:not(.tmenusel) span.tmenulabel { display: none !important; }
	ul.tmenu li:not(.tmenusel) div.tmenucenter { padding: 0 11px !important; gap: 0; }
	ul.tmenu li:not(.tmenusel) div.tmenucenter::after { left: 8px; right: 8px; }
}
@media only screen and (max-width: 1439px) {
	div#tmenu_tooltip, div#tmenu_tooltipinvert { padding-right: 280px !important; }
	div.login_block a .atoploginusername { max-width: 100px !important; }
}
@media only screen and (max-width: 1199px) {
	div.fiche { margin: 20px 20px 28px !important; }
	div.login_block span.aversion { display: none !important; }
	div#tmenu_tooltip, div#tmenu_tooltipinvert { padding-right: 230px !important; }
}
@media only screen and (max-width: 991px) {
	:root { --sidebar-w: 272px; }
	/* Off-canvas sidebar opened by the burger injected by JS */
	.side-nav {
		transform: translateX(-100%) !important;
		transition: transform .22s ease !important;
		box-shadow: var(--shadow-lg) !important;
		z-index: 1020 !important;
		display: block !important;
	}
	html.mui-nav-open .side-nav { transform: translateX(0) !important; }
	html.mui-nav-open body::after {
		content: "";
		position: fixed;
		inset: var(--topbar-h) 0 0 0;
		background: rgba(15, 23, 42, .35);
		z-index: 1015;
	}
	#id-right { padding-left: 0 !important; }
	.mui-brand { width: auto; border-right: 0; padding: 0 12px 0 60px; background: transparent; border-bottom: 0; }
	.mui-brand .mui-brand-version { display: none; }
	.mui-burger { display: inline-flex !important; }
	div#tmenu_tooltip, div#tmenu_tooltipinvert { padding-left: 224px !important; padding-right: 150px !important; }
	li.menuhider, li#mainmenutd_menu { display: none !important; }
	div.login_block > div.login_block_other { display: none !important; }
	div.fichehalfleft, div.fichehalfright, div.fichethirdleft, div.fichetwothirdright { width: 100% !important; float: none !important; }
	.mui-login-hero { display: none; }
	body.bodylogin .login_center { flex: 1 1 100%; max-width: 100%; }
}
@media only screen and (max-width: 767px) {
	:root { --topbar-h: 56px; }
	div.fiche { margin: 16px 16px 24px !important; }
	div.tabBar, div.tabBar.tabBarWithBottom { padding: 16px !important; }
	.mui-brand-name { display: none; }
	.mui-brand { padding-left: 56px; }
	div#tmenu_tooltip, div#tmenu_tooltipinvert { padding-left: 100px !important; padding-right: 64px !important; }
	div.login_block > div.login_block_tools { display: none !important; }
	div.login_block > div.login_block_user { border-left: 0 !important; padding-left: 0 !important; margin-left: 0 !important; }
	/* Title bar wraps: title on first line, actions below */
	table.table-fiche-title, table.table-fiche-title > tbody, table.table-fiche-title > tbody > tr { display: flex !important; flex-wrap: wrap; align-items: center; width: 100%; }
	table.table-fiche-title td.col-right, table.table-fiche-title td.col-center { margin-left: auto; max-width: 100%; }
	table.table-fiche-title td.col-center { order: 3; width: 100%; text-align: left !important; }
	div.login_block a .atoploginusername { display: none !important; }
	div.login_block_user .dropdown-toggle::after { display: none; }
	table.mui-page-title .titre, table.mui-page-title div.titre { font-size: 19px !important; }
	table.mui-page-title td.col-picto .pictotitle { width: 34px !important; height: 34px !important; font-size: 16px !important; }
	table.table-fiche-title tr { flex-wrap: wrap; }
	div.box-flex-container.kanban, .box-flex-container.kanban { grid-template-columns: 1fr; }
	div.liste_titre.liste_titre_bydiv, div.divsearchfieldfilter { padding: 10px 12px !important; }
	div.tabs { overflow-x: auto; flex-wrap: nowrap; scrollbar-width: none; }
	div.tabs::-webkit-scrollbar { display: none; }
	a.tab:link, a.tab:visited, a.tab { padding: 10px 10px !important; white-space: nowrap; }
	.jnotify-container { right: 12px !important; width: calc(100vw - 24px) !important; }
	body.bodylogin .login_center { padding: 24px 20px; align-items: flex-start; padding-top: 12vh; }
	.mui-login-heading h1 { font-size: 22px; }
	.ui-dialog { max-width: calc(100vw - 24px) !important; }
}
@media only screen and (max-width: 420px) {
	.info-box-module .info-box { padding: 16px !important; }
}

/* Burger button (injected by JS, visible below 992px) */
.mui-burger {
	display: none;
	position: fixed;
	top: calc((var(--topbar-h) - 40px) / 2);
	left: 12px;
	z-index: 1011;
	width: 40px;
	height: 40px;
	align-items: center;
	justify-content: center;
	border: 1px solid var(--border);
	border-radius: var(--radius-control);
	background: var(--bg-surface);
	color: var(--text-secondary);
	cursor: pointer;
	font-size: 16px;
	padding: 0;
}
.mui-burger:hover { background: var(--bg-hover); color: var(--text-primary); }

<?php if ($hidetop || $hideleft) { ?>
/* Popup / embedded mode (dol_hide_topmenu / dol_hide_leftmenu) */
.mui-brand, .mui-burger { display: none !important; }
#id-right { padding-left: 0 !important; }
<?php } ?>

/* --------------------------------------------------------------------------
   19. Dark mode (opt-in: add data-mui-theme="dark" on <html>, see README)
   Only variables are changed.
   -------------------------------------------------------------------------- */
html[data-mui-theme="dark"] {
	--primary: #818CF8;
	--primary-hover: #A5B4FC;
	--primary-soft: rgba(129, 140, 248, .14);
	--primary-ring: rgba(129, 140, 248, .28);
	--bg-app: #0B1120;
	--bg-surface: #111827;
	--bg-sidebar: #0F172A;
	--bg-hover: #1E293B;
	--bg-subtle: #0F172A;
	--border: #1F2A3C;
	--border-strong: #334155;
	--text-primary: #E2E8F0;
	--text-secondary: #94A3B8;
	--text-muted: #64748B;
	--success-soft: rgba(16, 185, 129, .12);
	--warning-soft: rgba(245, 158, 11, .12);
	--danger-soft: rgba(239, 68, 68, .12);
	--info-soft: rgba(59, 130, 246, .12);
	--fam-hr-bg: rgba(139, 92, 246, .14);
	--fam-crm-bg: rgba(59, 130, 246, .14);
	--fam-fin-bg: rgba(16, 185, 129, .14);
	--fam-prod-bg: rgba(249, 115, 22, .14);
	--fam-proj-bg: rgba(236, 72, 153, .14);
	--fam-other-bg: rgba(100, 116, 139, .18);
	color-scheme: dark;
}
