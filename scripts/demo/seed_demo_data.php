#!/usr/bin/env php
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
 * \file        scripts/demo/seed_demo_data.php
 * \ingroup     core
 * \brief       Fill an EMPTY demo database with realistic Vietnamese sample data.
 *
 * Every record goes through the Dolibarr business classes (create/addline/validate/payment),
 * so references, totals, VAT, balances and bank lines are consistent with manual input.
 * Records carry import_key = 'DEMOSEED' when the table has this column.
 *
 * Usage:  php seed_demo_data.php [--login=admin] [--scale=1] [--no-company] [--force]
 *   --scale=N      multiply volumes (1 = ~150 thirdparties, ~200 invoices...)
 *   --no-company   do not set company name / country Vietnam / currency VND
 *   --force        run even if demo data was already seeded
 */

$sapi_type = php_sapi_name();
$script_file = basename(__FILE__);
$path = __DIR__.'/';

if (substr($sapi_type, 0, 3) == 'cgi') {
	echo "Error: You are using PHP for CGI. To execute ".$script_file." from command line, you must use PHP for CLI mode.\n";
	exit(1);
}

// Options
$opts = array('login' => 'admin', 'scale' => 1, 'no-company' => false, 'force' => false);
foreach (array_slice($argv, 1) as $arg) {
	if (preg_match('/^--(login|scale)=(.+)$/', $arg, $m)) {
		$opts[$m[1]] = $m[2];
	} elseif (preg_match('/^--(no-company|force)$/', $arg, $m)) {
		$opts[$m[1]] = true;
	} else {
		echo "Unknown option ".$arg."\nUsage: php ".$script_file." [--login=admin] [--scale=1] [--no-company] [--force]\n";
		exit(1);
	}
}
$scale = max(0.1, (float) $opts['scale']);

require_once $path."../../htdocs/master.inc.php";
require_once DOL_DOCUMENT_ROOT.'/core/lib/admin.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/date.lib.php';
require_once DOL_DOCUMENT_ROOT.'/societe/class/societe.class.php';
require_once DOL_DOCUMENT_ROOT.'/contact/class/contact.class.php';
require_once DOL_DOCUMENT_ROOT.'/product/class/product.class.php';
require_once DOL_DOCUMENT_ROOT.'/comm/propal/class/propal.class.php';
require_once DOL_DOCUMENT_ROOT.'/commande/class/commande.class.php';
require_once DOL_DOCUMENT_ROOT.'/compta/facture/class/facture.class.php';
require_once DOL_DOCUMENT_ROOT.'/compta/paiement/class/paiement.class.php';
require_once DOL_DOCUMENT_ROOT.'/compta/bank/class/account.class.php';
require_once DOL_DOCUMENT_ROOT.'/user/class/user.class.php';

/**
 * @var Conf $conf
 * @var DoliDB $db
 * @var Societe $mysoc
 * @var Translate $langs
 */

// Speed and side effects: no PDF generation, no email while seeding (in memory only, not saved)
$conf->global->MAIN_DISABLE_PDF_AUTOUPDATE = 1;
$conf->global->MAIN_DISABLE_ALL_MAILS = 1;
$conf->global->TICKET_DISABLE_ALL_MAILS = 1;

$langs->loadLangs(array('main', 'bills', 'companies'));

$user = new User($db);
if ($user->fetch(0, $opts['login']) <= 0) {
	echo "Error: user '".$opts['login']."' not found\n";
	exit(1);
}
$user->loadRights();
if (empty($user->admin)) {
	echo "Error: user '".$opts['login']."' must be an administrator\n";
	exit(1);
}

$already = (int) $db->getRow("SELECT COUNT(*) as nb FROM ".MAIN_DB_PREFIX."societe WHERE import_key = 'DEMOSEED'")->nb;
if ($already && !$opts['force']) {
	echo "Demo data already seeded (".$already." thirdparties with import_key=DEMOSEED). Use --force to add more.\n";
	exit(0);
}

mt_srand(20260924);
$now = dol_now();
$DAY = 86400;
$stats = array();
$errors = 0;

/*
 * Helpers
 */

/**
 * Pick a random element
 *
 * @param	array<mixed>	$arr	Array
 * @return	mixed
 */
function pick($arr)
{
	return $arr[mt_rand(0, count($arr) - 1)];
}

/**
 * Random boolean with probability
 *
 * @param	float	$p	Probability (0..1)
 * @return	bool
 */
function chance($p)
{
	return (mt_rand() / mt_getrandmax()) < $p;
}

/**
 * Report an error of a business object and continue
 *
 * @param	string			$what	Context
 * @param	CommonObject	$obj	Object
 * @return	void
 */
function fail($what, $obj)
{
	global $errors, $db;
	$errors++;
	$msg = trim($obj->error.' '.implode(' | ', (array) $obj->errors));
	if (!$msg && $db->lasterror()) {
		$msg = 'SQL: '.$db->lasterror();
	}
	echo "  ! ".$what.": ".($msg ? $msg : 'unknown error')."\n";
}

/**
 * Count a created record
 *
 * @param	string	$key	Stat key
 * @return	void
 */
function created($key)
{
	global $stats;
	$stats[$key] = (isset($stats[$key]) ? $stats[$key] : 0) + 1;
}

/**
 * Tag a record created by the seeder
 *
 * @param	string	$table	Table without prefix
 * @param	int		$id		Row id
 * @return	void
 */
function tag($table, $id)
{
	global $db;
	$db->query("UPDATE ".MAIN_DB_PREFIX.$table." SET import_key = 'DEMOSEED' WHERE rowid = ".((int) $id));
}

/**
 * Email-safe ascii slug
 *
 * @param	string	$s	Text
 * @return	string
 */
function slug($s)
{
	$s = str_replace(array('đ', 'Đ'), array('d', 'D'), $s);
	return strtolower(preg_replace('/[^a-z0-9]+/i', '', dol_string_unaccent($s)));
}

/**
 * Last word of a string (Vietnamese given name)
 *
 * @param	string	$s	Text
 * @return	string
 */
function lastword($s)
{
	$w = explode(' ', trim($s));
	return end($w);
}

/**
 * Round a VND amount to thousands
 *
 * @param	float	$v	Value
 * @return	int
 */
function vnd($v)
{
	return (int) (round($v / 1000) * 1000);
}

/**
 * Print a section title
 *
 * @param	string	$t	Title
 * @return	void
 */
function section($t)
{
	echo "\n== ".$t."\n";
}

// Reference data (Vietnamese)
$HO = array('Nguyễn', 'Trần', 'Lê', 'Phạm', 'Hoàng', 'Huỳnh', 'Phan', 'Vũ', 'Võ', 'Đặng', 'Bùi', 'Đỗ', 'Hồ', 'Ngô', 'Dương', 'Lý', 'Trương', 'Đinh');
$DEM_M = array('Văn', 'Minh', 'Quốc', 'Hữu', 'Đức', 'Hoàng', 'Gia', 'Thành', 'Công', 'Anh');
$DEM_F = array('Thị', 'Ngọc', 'Thu', 'Thanh', 'Bảo', 'Mỹ', 'Kim', 'Phương', 'Hồng', 'Diệu');
$TEN_M = array('An', 'Bình', 'Dũng', 'Hải', 'Hiếu', 'Hùng', 'Khang', 'Long', 'Nam', 'Phong', 'Phúc', 'Quân', 'Sơn', 'Thắng', 'Trung', 'Tuấn', 'Việt', 'Khoa', 'Đạt', 'Tài');
$TEN_F = array('Anh', 'Châu', 'Hà', 'Hạnh', 'Hoa', 'Hương', 'Lan', 'Linh', 'Mai', 'Nga', 'Quỳnh', 'Tâm', 'Thảo', 'Trang', 'Vy', 'Yến', 'Nhung', 'Oanh', 'Dung', 'My');
$CITIES = array(
	array('Hà Nội', '100000', array('Cầu Giấy', 'Đống Đa', 'Hai Bà Trưng', 'Long Biên', 'Hoàng Mai', 'Thanh Xuân', 'Bắc Từ Liêm')),
	array('TP. Hồ Chí Minh', '700000', array('Quận 1', 'Quận 3', 'Quận 7', 'Bình Thạnh', 'Tân Bình', 'Gò Vấp', 'Thủ Đức')),
	array('Đà Nẵng', '550000', array('Hải Châu', 'Thanh Khê', 'Sơn Trà', 'Liên Chiểu')),
	array('Hải Phòng', '180000', array('Lê Chân', 'Ngô Quyền', 'Hồng Bàng')),
	array('Bình Dương', '820000', array('Thủ Dầu Một', 'Dĩ An', 'Thuận An')),
	array('Đồng Nai', '810000', array('Biên Hòa', 'Long Thành', 'Nhơn Trạch')),
	array('Bắc Ninh', '220000', array('TP. Bắc Ninh', 'Từ Sơn', 'Quế Võ')),
	array('Cần Thơ', '900000', array('Ninh Kiều', 'Cái Răng', 'Bình Thủy')),
);
$STREETS = array('Nguyễn Trãi', 'Lê Lợi', 'Trần Hưng Đạo', 'Hai Bà Trưng', 'Lý Thường Kiệt', 'Nguyễn Văn Linh', 'Võ Văn Kiệt', 'Phạm Văn Đồng', 'Điện Biên Phủ', 'Cách Mạng Tháng Tám', 'Quang Trung', 'Lê Duẩn');
$CO_PREFIX = array('Công ty TNHH', 'Công ty TNHH', 'Công ty Cổ phần', 'Công ty CP', 'Doanh nghiệp tư nhân');
$CO_TRADE = array('Thép', 'Nhựa', 'Cơ khí', 'Điện công nghiệp', 'Thực phẩm', 'Dược phẩm', 'Xây dựng', 'Bao bì', 'Hóa chất', 'Dệt may', 'Chế biến gỗ', 'Vận tải', 'Nông sản', 'Thiết bị y tế', 'Tự động hóa', 'Năng lượng', 'Giấy', 'Cao su', 'Kỹ thuật', 'Xuất nhập khẩu');
$CO_NAME = array('Minh Phát', 'An Khang', 'Hòa Bình', 'Thành Công', 'Việt Tiến', 'Phú Thịnh', 'Đại Nam', 'Hưng Thịnh', 'Tân Tiến', 'Sao Mai', 'Bình Minh', 'Hoàng Long', 'Kim Ngân', 'Phương Đông', 'Trường Sơn', 'Nam Việt', 'Đông Á', 'Thiên Phúc', 'Vạn Xuân', 'Hải Âu', 'Thái Bình Dương', 'Toàn Cầu', 'Ánh Dương', 'Tiến Phát', 'Gia Hưng', 'Quang Minh', 'Đức Thành', 'Lạc Hồng', 'Hùng Vương', 'Sông Đà');
$POSTE = array('Giám đốc', 'Phó giám đốc', 'Trưởng phòng mua hàng', 'Nhân viên mua hàng', 'Kế toán trưởng', 'Kế toán công nợ', 'Trưởng phòng kỹ thuật', 'Quản đốc xưởng', 'Thủ kho');

/**
 * Random Vietnamese person
 *
 * @return	array{0:string,1:string,2:string}	lastname, firstname, gender (man|woman)
 */
function person()
{
	global $HO, $DEM_M, $DEM_F, $TEN_M, $TEN_F;
	if (chance(0.55)) {
		return array(pick($HO), pick($DEM_M).' '.pick($TEN_M), 'man');
	}
	return array(pick($HO), pick($DEM_F).' '.pick($TEN_F), 'woman');
}

/**
 * Random phone
 *
 * @return	string
 */
function phone()
{
	return pick(array('090', '091', '093', '097', '098', '086', '088')).mt_rand(1000000, 9999999);
}

/**
 * Random date between two timestamps
 *
 * @param	int	$from	From
 * @param	int	$to		To
 * @return	int
 */
function between($from, $to)
{
	return mt_rand((int) $from, (int) max($from, $to));
}

/*
 * 0. Company setup (country Vietnam, currency VND) — needed for VAT rates and for a clean home page
 */
if (!$opts['no-company']) {
	section('Company setup');
	$nbinvoices = (int) $db->getRow("SELECT COUNT(*) as nb FROM ".MAIN_DB_PREFIX."facture")->nb;
	if (!getDolGlobalString('MAIN_INFO_SOCIETE_NOM')) {
		dolibarr_set_const($db, 'MAIN_INFO_SOCIETE_NOM', 'Công ty CP Thương mại Kỹ thuật Demo', 'chaine', 0, '', $conf->entity);
		dolibarr_set_const($db, 'MAIN_INFO_SOCIETE_ADDRESS', '125 Nguyễn Văn Linh, Phường Tân Phong, Quận 7', 'chaine', 0, '', $conf->entity);
		dolibarr_set_const($db, 'MAIN_INFO_SOCIETE_TOWN', 'TP. Hồ Chí Minh', 'chaine', 0, '', $conf->entity);
		dolibarr_set_const($db, 'MAIN_INFO_SOCIETE_ZIP', '700000', 'chaine', 0, '', $conf->entity);
		dolibarr_set_const($db, 'MAIN_INFO_SOCIETE_TEL', '02838123456', 'chaine', 0, '', $conf->entity);
		dolibarr_set_const($db, 'MAIN_INFO_SOCIETE_MAIL', 'info@demo-erp.vn', 'chaine', 0, '', $conf->entity);
		dolibarr_set_const($db, 'MAIN_INFO_SIREN', '0312345678', 'chaine', 0, '', $conf->entity);
		echo "  company name/address set\n";
	}
	if (!getDolGlobalString('MAIN_INFO_SOCIETE_COUNTRY')) {
		dolibarr_set_const($db, 'MAIN_INFO_SOCIETE_COUNTRY', '233:VN:Vietnam', 'chaine', 0, '', $conf->entity);
		echo "  country set to Vietnam\n";
	}
	if ($nbinvoices == 0 && getDolGlobalString('MAIN_MONNAIE') != 'VND') {
		dolibarr_set_const($db, 'MAIN_MONNAIE', 'VND', 'chaine', 0, '', $conf->entity);
		dolibarr_set_const($db, 'MAIN_MAX_DECIMALS_UNIT', '0', 'chaine', 0, '', $conf->entity);
		dolibarr_set_const($db, 'MAIN_MAX_DECIMALS_TOT', '0', 'chaine', 0, '', $conf->entity);
		dolibarr_set_const($db, 'MAIN_MAX_DECIMALS_SHOWN', '0', 'chaine', 0, '', $conf->entity);
		echo "  currency set to VND (0 decimals)\n";
	}
	$conf->setValues($db);
	$mysoc->setMysoc($conf);
}
$currency = getDolGlobalString('MAIN_MONNAIE', 'VND');
$countryid = 233;
$VAT = ($mysoc->country_code == 'VN') ? array(10, 10, 10, 8, 5) : array(20, 20, 10);

/*
 * 1. Users / employees (HRM)
 */
section('Users & employees');
$DEPARTMENTS = array(
	'Kinh doanh' => array('Giám đốc kinh doanh', 'Trưởng nhóm kinh doanh', 'Nhân viên kinh doanh', 'Nhân viên kinh doanh', 'Sales admin'),
	'Mua hàng' => array('Trưởng phòng mua hàng', 'Nhân viên mua hàng'),
	'Kho vận' => array('Thủ kho', 'Nhân viên kho', 'Nhân viên giao nhận'),
	'Kế toán' => array('Kế toán trưởng', 'Kế toán công nợ', 'Kế toán kho'),
	'Kỹ thuật' => array('Trưởng phòng kỹ thuật', 'Kỹ sư dịch vụ', 'Kỹ thuật viên'),
	'Nhân sự' => array('Chuyên viên nhân sự'),
);
$employees = array();
$sales = array();
$managers = array();
$nbusers = (int) round(32 * $scale);
$usedlogins = array();
for ($i = 0; $i < $nbusers; $i++) {
	list($ln, $fn, $gender) = person();
	$dept = array_keys($DEPARTMENTS)[$i % count($DEPARTMENTS)];
	$jobs = $DEPARTMENTS[$dept];
	$isHead = !isset($managers[$dept]);
	$job = $isHead ? $jobs[0] : pick(array_slice($jobs, 1) ?: $jobs);
	$words = explode(' ', $fn);
	$login = slug(end($words)).slug(mb_substr($ln, 0, 1)).slug(mb_substr($words[0], 0, 1));
	$base = $login;
	$n = 1;
	while (isset($usedlogins[$login]) || (int) $db->getRow("SELECT COUNT(*) as nb FROM ".MAIN_DB_PREFIX."user WHERE login = '".$db->escape($login)."'")->nb) {
		$login = $base.(++$n);
	}
	$usedlogins[$login] = 1;

	$u = new User($db);
	$u->login = $login;
	$u->lastname = $ln;
	$u->firstname = $fn;
	$u->gender = $gender;
	$u->email = $login.'@demo-erp.vn';
	$u->office_phone = '028381234'.str_pad((string) (10 + $i), 2, '0', STR_PAD_LEFT);
	$u->user_mobile = phone();
	$u->job = $job;
	$u->employee = 1;
	$u->statut = 1;
	$u->status = 1;
	$u->salary = vnd(mt_rand(9, 45) * 1000000);
	$u->weeklyhours = 48;
	$u->dateemployment = $now - mt_rand(90, 2500) * $DAY;
	$u->birth = $now - mt_rand(23, 52) * 365 * $DAY;
	$u->note_public = 'Phòng '.$dept;
	if (!$isHead) {
		$u->fk_user = $managers[$dept];
	}
	$res = $u->create($user);
	if ($res <= 0) {
		fail('user '.$login, $u);
		continue;
	}
	$u->setPassword($user, 'Demo@2026', 0, 0, 1);
	tag('user', $u->id);
	created('Người dùng / nhân viên');
	$employees[] = $u->id;
	if ($isHead) {
		$managers[$dept] = $u->id;
	}
	if ($dept == 'Kinh doanh') {
		$sales[] = $u->id;
	}
}
if (!$sales) {
	$sales = array($user->id);
}
$allusers = array_merge(array($user->id), $employees);

/*
 * 2. Bank accounts
 */
section('Bank accounts');
$banks = array();
$BANKS = array(
	array('VCB-HCM', 'Vietcombank - CN Hồ Chí Minh', 'Ngân hàng TMCP Ngoại thương Việt Nam', 1, 850000000, '0071001234567'),
	array('TCB-Q7', 'Techcombank - CN Quận 7', 'Ngân hàng TMCP Kỹ thương Việt Nam', 1, 420000000, '19033344455566'),
	array('ACB-TB', 'ACB - CN Tân Bình', 'Ngân hàng TMCP Á Châu', 1, 260000000, '220088899'),
	array('CTG-HN', 'VietinBank - CN Hà Nội', 'Ngân hàng TMCP Công thương Việt Nam', 1, 150000000, '108000777666'),
	array('QUY-TM', 'Quỹ tiền mặt văn phòng', '', 2, 60000000, ''),
);
foreach ($BANKS as $b) {
	$acc = new Account($db);
	$acc->ref = $b[0];
	$acc->label = $b[1];
	$acc->bank = $b[2];
	$acc->type = $b[3];
	$acc->courant = $b[3];
	$acc->number = $b[5];
	$acc->currency_code = $currency;
	$acc->country_id = $countryid;
	$acc->date_solde = $now - 400 * $DAY;
	$acc->balance = $b[4];
	$acc->solde = $b[4];
	$acc->min_allowed = 0;
	$acc->min_desired = 50000000;
	$acc->status = 0;
	$acc->clos = 0;
	$acc->owner_name = getDolGlobalString('MAIN_INFO_SOCIETE_NOM');
	$res = $acc->create($user);
	if ($res <= 0) {
		fail('bank '.$b[0], $acc);
		continue;
	}
	created('Tài khoản ngân hàng');
	$banks[] = $acc->id;
}

/*
 * 3. Products & services
 */
section('Products & services');
$CATALOG = array(
	// label, price HT, unit desc
	array('Vòng bi SKF 6205-2RS', 85000), array('Vòng bi NSK 6308ZZ', 210000), array('Vòng bi FAG 22215-E1', 1850000), array('Gối đỡ UCP 208', 320000),
	array('Dầu thủy lực Shell Tellus S2 M46 (phuy 209L)', 16500000), array('Dầu động cơ Castrol CRB 15W-40 (xô 18L)', 1650000), array('Mỡ bôi trơn Mobilux EP2 (xô 18kg)', 2350000),
	array('Van bi inox 304 DN50', 780000), array('Van cổng gang DN100', 2450000), array('Van điện từ Burkert 1/2"', 1950000), array('Van an toàn DN25', 1150000),
	array('Máy bơm ly tâm Ebara 3HP', 12800000), array('Máy bơm chìm Tsurumi 2HP', 9600000), array('Động cơ điện 3 pha Toshiba 5.5kW', 11200000), array('Hộp giảm tốc NMRV 063 tỉ số 1/30', 3450000),
	array('Biến tần Mitsubishi FR-E720 2.2kW', 7900000), array('Biến tần Schneider ATV320 4kW', 11800000), array('PLC Siemens S7-1200 CPU 1214C', 9850000), array('Màn hình HMI Weintek 7 inch', 6300000),
	array('Cảm biến tiệm cận Omron E2E-X5ME1', 690000), array('Cảm biến quang Autonics BJ100', 540000), array('Encoder Omron E6B2 1000P/R', 1450000),
	array('Contactor Schneider LC1D32', 1180000), array('Aptomat LS ABN 3P 100A', 1650000), array('Rơ le nhiệt Schneider LRD 25A', 620000), array('Tủ điện điều khiển 800x600', 8900000),
	array('Cáp điện Cadivi CVV 4x16 (mét)', 185000), array('Cáp điều khiển Sangjin 10x1.5 (mét)', 72000),
	array('Dây curoa Bando B-60', 145000), array('Xích công nghiệp RS60 (hộp 3m)', 780000), array('Khớp nối mềm FCL 160', 890000), array('Ống thủy lực Parker 3/8" (mét)', 165000),
	array('Bulong inox M12x50 (hộp 100 con)', 520000), array('Keo khóa ren Loctite 243 (50ml)', 385000),
	array('Găng tay bảo hộ 3M (hộp 12 đôi)', 240000), array('Mũ bảo hộ Protector', 95000), array('Giày bảo hộ Jogger Bestrun', 1250000), array('Kính bảo hộ 3M 1621', 110000),
	array('Máy khoan Bosch GSB 13 RE', 1390000), array('Máy mài góc Makita 9553NB', 1180000), array('Máy nén khí Pegasus 3HP 100L', 6950000), array('Quạt công nghiệp Deton 750W', 3250000),
	array('Đồng hồ áp suất Wika 0-10 bar', 460000), array('Đồng hồ nhiệt độ Autonics TC4S', 890000), array('Pa lăng xích Kawasaki 2 tấn', 3600000), array('Xe nâng tay 2.5 tấn', 5400000),
	array('Băng tải cao su B500 (mét)', 1250000), array('Con lăn băng tải D60', 145000), array('Xi lanh khí nén Airtac SC63x100', 1350000), array('Van khí nén Airtac 4V210-08', 520000),
);
$SERVICES = array(
	array('Dịch vụ lắp đặt thiết bị', 3500000), array('Bảo trì định kỳ hệ thống (tháng)', 6500000), array('Tư vấn kỹ thuật (giờ)', 450000), array('Vận chuyển nội thành', 650000),
	array('Hiệu chuẩn thiết bị đo', 1200000), array('Sửa chữa động cơ điện', 2800000), array('Đào tạo vận hành (buổi)', 4000000), array('Thuê kỹ thuật viên (ngày)', 1500000),
	array('Lập trình PLC / HMI', 9000000), array('Khảo sát hiện trạng nhà máy', 2500000),
);
$products = array();
$nbproducts = (int) round(count($CATALOG) * max(1, $scale));
for ($i = 0; $i < $nbproducts; $i++) {
	$c = $CATALOG[$i % count($CATALOG)];
	$variant = (int) floor($i / count($CATALOG));
	$p = new Product($db);
	$p->ref = sprintf('SP%04d', $i + 1);
	$p->label = $c[0].($variant ? ' - loại '.chr(65 + $variant) : '');
	$p->description = 'Hàng chính hãng, bảo hành 12 tháng. Xuất xứ: '.pick(array('Nhật Bản', 'Đức', 'Hàn Quốc', 'Thái Lan', 'Việt Nam', 'Trung Quốc', 'Ý')).'.';
	$p->type = Product::TYPE_PRODUCT;
	$p->price = vnd($c[1] * (1 + 0.08 * $variant));
	$p->price_base_type = 'HT';
	$p->tva_tx = pick($VAT);
	$p->status = 1;
	$p->status_buy = 1;
	$p->cost_price = vnd($p->price * mt_rand(62, 80) / 100);
	$p->weight = mt_rand(1, 400) / 10;
	$p->weight_units = 0;
	$res = $p->create($user);
	if ($res <= 0) {
		fail('product '.$p->ref, $p);
		continue;
	}
	tag('product', $p->id);
	created('Sản phẩm');
	$products[] = array('id' => $p->id, 'label' => $p->label, 'price' => $p->price, 'tva' => $p->tva_tx, 'type' => 0);
}
foreach ($SERVICES as $i => $c) {
	$p = new Product($db);
	$p->ref = sprintf('DV%03d', $i + 1);
	$p->label = $c[0];
	$p->type = Product::TYPE_SERVICE;
	$p->price = vnd($c[1]);
	$p->price_base_type = 'HT';
	$p->tva_tx = pick($VAT);
	$p->status = 1;
	$p->status_buy = 0;
	$res = $p->create($user);
	if ($res <= 0) {
		fail('service '.$p->ref, $p);
		continue;
	}
	tag('product', $p->id);
	created('Dịch vụ');
	$products[] = array('id' => $p->id, 'label' => $p->label, 'price' => $p->price, 'tva' => $p->tva_tx, 'type' => 1);
}

/*
 * 4. Thirdparties + contacts
 */
section('Thirdparties & contacts');
$customers = array();
$prospects = array();
$suppliers = array();
$nbsoc = (int) round(150 * $scale);
$usednames = array();
for ($i = 0; $i < $nbsoc; $i++) {
	do {
		$name = pick($CO_PREFIX).' '.pick($CO_TRADE).' '.pick($CO_NAME);
	} while (isset($usednames[$name]) && count($usednames) < 5000);
	$usednames[$name] = 1;
	$city = pick($CITIES);
	$kind = ($i % 10 < 6) ? 'customer' : (($i % 10 < 8) ? 'prospect' : 'supplier');

	$s = new Societe($db);
	$s->name = $name;
	$s->name_alias = pick($CO_NAME);
	$s->client = ($kind == 'customer') ? 1 : (($kind == 'prospect') ? 2 : 0);
	$s->fournisseur = ($kind == 'supplier' || chance(0.05)) ? 1 : 0;
	$s->code_client = ($s->client ? -1 : '');
	$s->code_fournisseur = ($s->fournisseur ? -1 : '');
	$s->address = mt_rand(1, 450).' '.pick($STREETS).', '.pick($city[2]);
	$s->zip = $city[1];
	$s->town = $city[0];
	$s->country_id = $countryid;
	$s->phone = '0'.pick(array('24', '28', '236', '225', '274', '251')).mt_rand(3000000, 9999999);
	$s->email = 'info@'.substr(slug($name), -18).mt_rand(1, 99).'.vn';
	$s->url = 'https://www.'.substr(slug($name), -18).'.vn';
	$s->idprof1 = '03'.mt_rand(10000000, 99999999);
	$s->tva_intra = $s->idprof1;
	$s->tva_assuj = 1;
	$s->status = 1;
	$s->cond_reglement_id = pick(array(1, 2, 2, 3, 4));
	$s->mode_reglement_id = pick(array(2, 2, 2, 7, 4));
	$s->outstanding_limit = ($kind == 'customer') ? pick(array(200000000, 300000000, 500000000, 1000000000)) : '';
	$s->note_public = ($kind == 'customer' && chance(0.3)) ? 'Khách hàng thân thiết, ưu tiên giao hàng.' : '';
	$s->import_key = 'DEMOSEED';
	$res = $s->create($user);
	if ($res <= 0) {
		fail('thirdparty '.$name, $s);
		continue;
	}
	tag('societe', $s->id);
	created($kind == 'customer' ? 'Khách hàng' : ($kind == 'prospect' ? 'KH tiềm năng' : 'Nhà cung cấp'));
	if ($s->client) {
		$s->add_commercial($user, pick($sales));
	}
	if ($kind == 'customer') {
		$customers[] = $s->id;
	} elseif ($kind == 'prospect') {
		$prospects[] = $s->id;
	} else {
		$suppliers[] = $s->id;
	}

	$nbc = mt_rand(1, 3);
	for ($j = 0; $j < $nbc; $j++) {
		list($ln, $fn, $gender) = person();
		$ct = new Contact($db);
		$ct->socid = $s->id;
		$ct->lastname = $ln;
		$ct->firstname = $fn;
		$ct->civility_code = ($gender == 'man') ? 'MR' : 'MME';
		$ct->poste = pick($POSTE);
		$ct->email = slug(lastword($fn)).'.'.slug($ln).mt_rand(1, 99).'@'.substr(slug($name), -12).'.vn';
		$ct->phone_pro = $s->phone;
		$ct->phone_mobile = phone();
		$ct->address = $s->address;
		$ct->zip = $s->zip;
		$ct->town = $s->town;
		$ct->country_id = $countryid;
		$ct->birthday = chance(0.4) ? ($now - mt_rand(25, 55) * 365 * $DAY + mt_rand(-20, 30) * $DAY) : '';
		$ct->statut = 1;
		if ($ct->create($user) <= 0) {
			fail('contact', $ct);
			continue;
		}
		tag('socpeople', $ct->id);
		created('Liên hệ');
	}
}
if (!$customers) {
	echo "No customer created, stop.\n";
	exit(1);
}

/**
 * Add 1..N random lines to a document
 *
 * @param	CommonObject	$doc	Propal|Commande|Facture
 * @param	int				$min	Min lines
 * @param	int				$max	Max lines
 * @return	void
 */
function addLines($doc, $min = 1, $max = 6)
{
	global $products;
	$nb = mt_rand($min, $max);
	for ($k = 0; $k < $nb; $k++) {
		$pr = pick($products);
		$qty = $pr['type'] ? mt_rand(1, 4) : pick(array(1, 2, 2, 3, 5, 5, 10, 12, 20, 24, 50));
		$remise = pick(array(0, 0, 0, 0, 3, 5, 5, 8, 10));
		if ($doc instanceof Facture) {
			$r = $doc->addline('', $pr['price'], $qty, $pr['tva'], 0, 0, $pr['id'], $remise, '', '', 0, 0, 0, 'HT', 0, $pr['type']);
		} elseif ($doc instanceof Commande) {
			$r = $doc->addline('', $pr['price'], $qty, $pr['tva'], 0, 0, $pr['id'], $remise, 0, 0, 'HT', 0, '', '', $pr['type']);
		} else {
			$r = $doc->addline('', $pr['price'], $qty, $pr['tva'], 0, 0, $pr['id'], $remise, 'HT', 0, 0, $pr['type']);
		}
		if ($r <= 0) {
			fail(get_class($doc).' line', $doc);
		}
	}
}

/*
 * 5. Commercial proposals (quotes)
 */
section('Proposals');
$signedprops = array();
$nbprop = (int) round(120 * $scale);
$propdates = array();
for ($i = 0; $i < $nbprop; $i++) {
	$propdates[] = between($now - 330 * $DAY, $now - 1 * $DAY);
}
sort($propdates);
foreach ($propdates as $i => $d) {
	$socid = chance(0.8) ? pick($customers) : pick($prospects ?: $customers);
	$pr = new Propal($db);
	$pr->socid = $socid;
	$pr->date = $d;
	$pr->duree_validite = pick(array(15, 30, 30, 45));
	$pr->cond_reglement_id = pick(array(1, 2, 2, 3));
	$pr->mode_reglement_id = 2;
	$pr->delivery_date = $d + mt_rand(7, 45) * $DAY;
	$pr->ref_client = chance(0.4) ? 'YC-'.mt_rand(1000, 9999) : '';
	$pr->note_public = chance(0.3) ? 'Báo giá có hiệu lực trong thời gian ghi trên chứng từ. Giá đã bao gồm vận chuyển nội thành.' : '';
	$pr->user_author_id = pick($sales);
	if ($pr->create($user) <= 0) {
		fail('proposal', $pr);
		continue;
	}
	addLines($pr, 1, 7);
	created('Báo giá');
	$age = ($now - $d) / $DAY;
	if ($age < 5 && chance(0.6)) {
		continue; // recent drafts
	}
	if ($pr->valid($user) <= 0) {
		fail('proposal validate', $pr);
		continue;
	}
	if ($age > 20) {
		$r = mt_rand(1, 10);
		if ($r <= 6) {
			$pr->closeProposal($user, Propal::STATUS_SIGNED, 'Khách hàng đã ký duyệt');
			$signedprops[] = array('id' => $pr->id, 'socid' => $socid, 'date' => $d);
		} elseif ($r <= 8) {
			$pr->closeProposal($user, Propal::STATUS_NOTSIGNED, pick(array('Giá cao hơn đối thủ', 'Khách hàng hoãn dự án', 'Không đáp ứng thời gian giao hàng')));
		}
	}
}

/*
 * 6. Customer orders (mostly from signed proposals)
 */
section('Customer orders');
$orders = array();
$orderdates = array();
foreach ($signedprops as $sp) {
	$orderdates[] = array('date' => between($sp['date'] + 2 * $DAY, min($now, $sp['date'] + 20 * $DAY)), 'prop' => $sp);
}
$nbfree = (int) round(30 * $scale);
for ($i = 0; $i < $nbfree; $i++) {
	$orderdates[] = array('date' => between($now - 300 * $DAY, $now), 'prop' => null);
}
usort($orderdates, function ($a, $b) {
	return $a['date'] - $b['date'];
});
foreach ($orderdates as $od) {
	$d = $od['date'];
	if ($od['prop']) {
		$pr = new Propal($db);
		$pr->fetch($od['prop']['id']);
		$o = new Commande($db);
		$res = $o->createFromProposal($pr, $user);
		if ($res <= 0) {
			fail('order from proposal', $o);
			continue;
		}
		$o->fetch($o->id > 0 ? $o->id : $res);
		$db->query("UPDATE ".MAIN_DB_PREFIX."commande SET date_commande = '".$db->idate($d)."' WHERE rowid = ".((int) $o->id));
		$o->date = $d;
		$socid = $pr->socid;
	} else {
		$socid = pick($customers);
		$o = new Commande($db);
		$o->socid = $socid;
		$o->date = $d;
		$o->date_commande = $d;
		$o->cond_reglement_id = pick(array(1, 2, 2, 3));
		$o->mode_reglement_id = 2;
		$o->delivery_date = $d + mt_rand(3, 30) * $DAY;
		$o->ref_client = 'PO-'.mt_rand(10000, 99999);
		if ($o->create($user) <= 0) {
			fail('order', $o);
			continue;
		}
		addLines($o, 1, 6);
		$o->fetch($o->id);
	}
	created('Đơn hàng bán');
	$age = ($now - $d) / $DAY;
	if ($age < 3 && chance(0.5)) {
		continue;
	}
	if ($o->valid($user) <= 0) {
		fail('order validate', $o);
		continue;
	}
	if ($age > 25 && chance(0.7)) {
		$o->cloture($user);
	} elseif ($age > 8 && chance(0.5)) {
		$o->setStatut(Commande::STATUS_SHIPMENTONPROCESS);
	}
	$orders[] = array('id' => $o->id, 'socid' => $socid, 'date' => $d);
}

/*
 * 7. Customer invoices + payments + bank lines
 */
section('Customer invoices & payments');
$invdocs = array();
foreach ($orders as $od) {
	if (chance(0.75)) {
		$invdocs[] = array('date' => min($now, $od['date'] + mt_rand(1, 12) * $DAY), 'order' => $od);
	}
}
$nbfreeinv = (int) round(110 * $scale);
for ($i = 0; $i < $nbfreeinv; $i++) {
	$invdocs[] = array('date' => between($now - 360 * $DAY, $now), 'order' => null);
}
usort($invdocs, function ($a, $b) {
	return $a['date'] - $b['date'];
});
$paymodes = array(2 => 'VIR', 7 => 'CHQ', 4 => 'LIQ');
foreach ($invdocs as $iv) {
	$d = $iv['date'];
	$f = new Facture($db);
	if ($iv['order']) {
		$o = new Commande($db);
		$o->fetch($iv['order']['id']);
		$res = $f->createFromOrder($o, $user);
		if ($res <= 0) {
			fail('invoice from order', $f);
			continue;
		}
		$f->fetch($f->id);
		$f->date = $d;
		$db->query("UPDATE ".MAIN_DB_PREFIX."facture SET datef = '".$db->idate($d)."', date_lim_reglement = '".$db->idate($f->calculate_date_lim_reglement())."' WHERE rowid = ".((int) $f->id));
		$f->fetch($f->id);
		$o->classifyBilled($user);
	} else {
		$f->socid = pick($customers);
		$f->type = Facture::TYPE_STANDARD;
		$f->date = $d;
		$f->cond_reglement_id = pick(array(1, 2, 2, 2, 3, 4));
		$f->mode_reglement_id = pick(array(2, 2, 7, 4));
		$f->ref_client = chance(0.3) ? 'HĐ-'.mt_rand(100, 999).'/'.date('Y', $d) : '';
		if ($f->create($user) <= 0) {
			fail('invoice', $f);
			continue;
		}
		addLines($f, 1, 5);
		$f->fetch($f->id);
	}
	created('Hóa đơn bán');
	$age = ($now - $d) / $DAY;
	if ($age < 2 && chance(0.5)) {
		continue; // a few drafts
	}
	if ($f->validate($user) <= 0) {
		fail('invoice validate '.$f->ref.' (id '.$f->id.', '.dol_print_date($f->date, 'day').', '.($iv['order'] ? 'from order' : 'direct').')', $f);
		continue;
	}
	$f->fetch($f->id);

	// Payment profile: most old invoices paid, some partially paid, some overdue (credit control demo)
	$due = $f->date_lim_reglement ? $f->date_lim_reglement : $d;
	$r = mt_rand(1, 100);
	if ($age > 20 && $r <= 72) {
		$amount = $f->total_ttc;
	} elseif ($age > 10 && $r <= 86) {
		$amount = vnd($f->total_ttc * pick(array(0.3, 0.5, 0.5, 0.7)));
	} else {
		$amount = 0; // unpaid (overdue if due date passed)
	}
	if ($amount > 0 && $banks) {
		$modeid = pick(array_keys($paymodes));
		if ($modeid == 4 && $amount > 20000000) {
			$modeid = 2; // large amounts are paid by bank transfer, not cash
		}
		$pay = new Paiement($db);
		$pay->datepaye = min($now, max($d, $due - mt_rand(-10, 25) * $DAY));
		$pay->amounts = array($f->id => $amount);
		$pay->multicurrency_amounts = array($f->id => $amount);
		$pay->multicurrency_code = array($f->id => $currency);
		$pay->multicurrency_tx = array($f->id => 1);
		$pay->paiementid = $modeid;
		$pay->paiementcode = $paymodes[$modeid];
		$pay->num_payment = ($modeid == 7) ? 'CHQ'.mt_rand(100000, 999999) : (($modeid == 2) ? 'UNC'.mt_rand(10000, 99999) : '');
		$pay->note_private = 'Thanh toán hóa đơn '.$f->ref;
		$pay->ext_payment_id = '';
		$pid = $pay->create($user, 1);
		if ($pid <= 0) {
			fail('payment '.$f->ref, $pay);
			continue;
		}
		$accountid = ($modeid == 4) ? end($banks) : pick(array_slice($banks, 0, max(1, count($banks) - 1)));
		$thirdparty = new Societe($db);
		$thirdparty->fetch($f->socid);
		if ($pay->addPaymentToBank($user, 'payment', '(CustomerInvoicePayment)', $accountid, $thirdparty->name, '') <= 0) {
			fail('payment bank line', $pay);
		}
		created('Thanh toán khách hàng');
	}
}

/*
 * 8. Other bank transactions (expenses, transfers...)
 */
section('Bank transactions');
$OPS = array(
	array('Thanh toán tiền thuê văn phòng', -45000000, 'VIR'), array('Chi lương nhân viên', -380000000, 'VIR'), array('Nộp bảo hiểm xã hội', -72000000, 'VIR'),
	array('Thanh toán tiền điện', -8500000, 'VIR'), array('Phí dịch vụ ngân hàng', -350000, 'PRE'), array('Chi tạm ứng công tác', -5000000, 'LIQ'),
	array('Thanh toán nhà cung cấp', -120000000, 'VIR'), array('Lãi tiền gửi', 1250000, 'VIR'), array('Nộp thuế GTGT', -65000000, 'VIR'),
	array('Chi mua văn phòng phẩm', -2400000, 'LIQ'), array('Thu tiền mặt bán lẻ', 15000000, 'LIQ'), array('Chi tiếp khách', -3500000, 'LIQ'),
);
$nbops = (int) round(90 * $scale);
if ($banks) {
	for ($i = 0; $i < $nbops; $i++) {
		$op = pick($OPS);
		$accid = ($op[2] == 'LIQ') ? end($banks) : pick(array_slice($banks, 0, max(1, count($banks) - 1)));
		$acc = new Account($db);
		$acc->fetch($accid);
		$amount = vnd($op[1] * mt_rand(70, 130) / 100);
		$d = between($now - 360 * $DAY, $now);
		$res = $acc->addline($d, $op[2], $op[0], $amount, '', 0, $user);
		if ($res <= 0) {
			fail('bank line', $acc);
			continue;
		}
		created('Giao dịch ngân hàng khác');
	}
}

// Keep every account with a realistic positive balance (random expenses may exceed receipts)
foreach ($banks as $k => $accid) {
	$acc = new Account($db);
	$acc->fetch($accid);
	$balance = (float) $acc->solde(1);
	$target = ($acc->type == Account::TYPE_CASH) ? 40000000 : 150000000;
	if ($balance < $target) {
		$amount = vnd($target - $balance + mt_rand(100, 600) * 1000000 * ($acc->type == Account::TYPE_CASH ? 0.1 : 1));
		$label = ($acc->type == Account::TYPE_CASH) ? 'Rút tiền gửi ngân hàng nhập quỹ' : 'Điều chuyển vốn từ tài khoản công ty mẹ';
		if ($acc->addline($now - mt_rand(200, 330) * $DAY, ($acc->type == Account::TYPE_CASH) ? 'LIQ' : 'VIR', $label, $amount, '', 0, $user) > 0) {
			created('Giao dịch ngân hàng khác');
		} else {
			fail('bank balance line', $acc);
		}
	}
}

/*
 * 9. Contracts, interventions, tickets
 */
if (isModEnabled('contrat')) {
	section('Contracts');
	require_once DOL_DOCUMENT_ROOT.'/contrat/class/contrat.class.php';
	$services = array_values(array_filter($products, function ($p) {
		return $p['type'] == 1;
	}));
	$nb = (int) round(25 * $scale);
	for ($i = 0; $i < $nb; $i++) {
		$d = between($now - 300 * $DAY, $now - 5 * $DAY);
		$c = new Contrat($db);
		$c->socid = pick($customers);
		$c->date_contrat = $d;
		$c->commercial_signature_id = pick($sales);
		$c->commercial_suivi_id = pick($sales);
		$c->ref_customer = 'HĐDV-'.date('Y', $d).'-'.mt_rand(100, 999);
		$c->note_public = 'Hợp đồng bảo trì định kỳ hệ thống thiết bị.';
		if ($c->create($user) <= 0) {
			fail('contract', $c);
			continue;
		}
		$sv = $services ? pick($services) : pick($products);
		$c->addline($sv['label'], $sv['price'], pick(array(1, 3, 6, 12)), $sv['tva'], 0, 0, $sv['id'], 0, $d, $d + 365 * $DAY);
		created('Hợp đồng');
		if (chance(0.8)) {
			$c->validate($user);
		}
	}
}
if (isModEnabled('ficheinter')) {
	section('Interventions');
	require_once DOL_DOCUMENT_ROOT.'/fichinter/class/fichinter.class.php';
	$TASKS = array('Kiểm tra và bảo dưỡng máy bơm', 'Thay vòng bi động cơ băng tải', 'Cài đặt biến tần dây chuyền 2', 'Hiệu chuẩn đồng hồ áp suất', 'Sửa chữa tủ điện điều khiển', 'Nâng cấp chương trình PLC');
	$nb = (int) round(30 * $scale);
	for ($i = 0; $i < $nb; $i++) {
		$d = between($now - 200 * $DAY, $now);
		$fi = new Fichinter($db);
		$fi->socid = pick($customers);
		$fi->description = pick($TASKS);
		$fi->author = $user->id;
		$fi->datec = $d;
		if ($fi->create($user) <= 0) {
			fail('intervention', $fi);
			continue;
		}
		$nbl = mt_rand(1, 3);
		for ($k = 0; $k < $nbl; $k++) {
			$fi->addline($user, $fi->id, pick($TASKS), $d + $k * $DAY, 3600 * mt_rand(1, 8));
		}
		created('Phiếu can thiệp');
		if (chance(0.7)) {
			$fi->fetch($fi->id);
			$fi->setValid($user);
		}
	}
}
if (isModEnabled('ticket')) {
	section('Tickets');
	require_once DOL_DOCUMENT_ROOT.'/ticket/class/ticket.class.php';
	$SUBJECTS = array(
		array('ISSUE', 'Máy bơm giao ngày %s bị rò rỉ phớt', 'HIGH'), array('ISSUE', 'Biến tần báo lỗi quá dòng khi khởi động', 'HIGH'),
		array('COM', 'Yêu cầu báo giá bổ sung vòng bi', 'NORMAL'), array('REQUEST', 'Đề nghị đổi hàng do sai mã sản phẩm', 'NORMAL'),
		array('HELP', 'Hướng dẫn cài đặt thông số PLC', 'LOW'), array('ISSUE', 'Giao hàng thiếu số lượng so với đơn hàng', 'BLOCKING'),
		array('COM', 'Khiếu nại thời gian giao hàng chậm', 'HIGH'), array('REQUEST', 'Yêu cầu test mẫu dầu thủy lực', 'NORMAL'),
	);
	$nb = (int) round(60 * $scale);
	for ($i = 0; $i < $nb; $i++) {
		$s = pick($SUBJECTS);
		$d = between($now - 180 * $DAY, $now);
		$t = new Ticket($db);
		$t->ref = $t->getDefaultRef();
		$t->subject = sprintf($s[1], dol_print_date($d - 5 * $DAY, 'day'));
		$t->message = "Kính gửi bộ phận chăm sóc khách hàng,\n\n".$t->subject.". Đề nghị quý công ty kiểm tra và phản hồi sớm.\n\nTrân trọng.";
		$t->type_code = $s[0];
		$t->category_code = 'OTHER';
		$t->severity_code = $s[2];
		$t->fk_soc = pick($customers);
		$t->fk_user_assign = pick($allusers);
		$t->datec = $d;
		$t->fk_statut = pick(array(0, 1, 1, 3, 5, 8, 8));
		$t->status = $t->fk_statut;
		$t->origin_email = 'khachhang'.mt_rand(1, 999).'@demo-kh.vn';
		if ($t->create($user) <= 0) {
			fail('ticket', $t);
			continue;
		}
		$db->query("UPDATE ".MAIN_DB_PREFIX."ticket SET datec = '".$db->idate($d)."' WHERE rowid = ".((int) $t->id));
		created('Ticket / khiếu nại');
	}
}

/*
 * 10. Members (Thành viên)
 */
if (isModEnabled('member')) {
	section('Members');
	require_once DOL_DOCUMENT_ROOT.'/adherents/class/adherent.class.php';
	require_once DOL_DOCUMENT_ROOT.'/adherents/class/adherent_type.class.php';
	$types = array();
	foreach (array(array('Hội viên chính thức', 1200000), array('Hội viên doanh nghiệp', 5000000), array('Hội viên danh dự', 0)) as $tt) {
		$at = new AdherentType($db);
		$at->label = $tt[0];
		$at->subscription = $tt[1] > 0 ? 1 : 0;
		$at->amount = $tt[1];
		$at->vote = 1;
		$at->status = 1;
		$at->morphy = '';
		$at->note_public = 'Loại hội viên '.$tt[0];
		if ($at->create($user) <= 0) {
			fail('member type', $at);
			continue;
		}
		$types[] = array('id' => $at->id, 'amount' => $tt[1]);
		created('Loại thành viên');
	}
	$nb = (int) round(80 * $scale);
	for ($i = 0; $i < $nb && $types; $i++) {
		list($ln, $fn, $gender) = person();
		$t = pick($types);
		$city = pick($CITIES);
		$m = new Adherent($db);
		$m->typeid = $t['id'];
		$m->lastname = $ln;
		$m->firstname = $fn;
		$m->civility_id = ($gender == 'man') ? 'MR' : 'MME';
		$m->gender = $gender;
		$m->morphy = ($t['amount'] >= 5000000) ? 'mor' : 'phy';
		$m->company = ($m->morphy == 'mor') ? pick($CO_PREFIX).' '.pick($CO_TRADE).' '.pick($CO_NAME) : '';
		$m->email = slug(lastword($fn)).'.'.slug($ln).mt_rand(1, 999).'@gmail.com';
		$m->phone_mobile = phone();
		$m->address = mt_rand(1, 300).' '.pick($STREETS);
		$m->zip = $city[1];
		$m->town = $city[0];
		$m->country_id = $countryid;
		$m->birth = $now - mt_rand(22, 60) * 365 * $DAY;
		$m->public = 0;
		$m->statut = -1;
		if ($m->create($user) <= 0) {
			fail('member', $m);
			continue;
		}
		created('Thành viên');
		if (chance(0.85)) {
			$m->validate($user);
			if ($t['amount'] > 0 && chance(0.8)) {
				$ds = $now - mt_rand(10, 330) * $DAY;
				$accountid = $banks ? $banks[0] : 0;
				$sid = $m->subscription($ds, $t['amount'], $accountid, 'VIR', 'Phí hội viên năm '.date('Y', $ds), '', '', '', dol_time_plus_duree($ds, 1, 'y') - $DAY);
				if ($sid > 0) {
					created('Đóng phí thành viên');
				} else {
					fail('subscription', $m);
				}
			}
		}
	}
}

/*
 * 11. HRM: jobs, skills, positions, leaves, recruitment
 */
if (isModEnabled('hrm')) {
	section('HRM');
	require_once DOL_DOCUMENT_ROOT.'/hrm/class/job.class.php';
	require_once DOL_DOCUMENT_ROOT.'/hrm/class/skill.class.php';
	require_once DOL_DOCUMENT_ROOT.'/hrm/class/position.class.php';
	$skills = array();
	$SKILLS = array(
		array('Kỹ năng đàm phán', 1), array('Kỹ năng giao tiếp khách hàng', 1), array('Tin học văn phòng', 0), array('Đọc bản vẽ kỹ thuật', 9),
		array('Lập trình PLC', 0), array('Kiến thức thiết bị điện công nghiệp', 9), array('Quản lý kho', 0), array('Nghiệp vụ kế toán', 9),
		array('Tiếng Anh giao tiếp', 1), array('An toàn lao động', 9), array('Làm việc nhóm', 1), array('Lái xe nâng', 0),
	);
	foreach ($SKILLS as $sk) {
		$o = new Skill($db);
		$o->label = $sk[0];
		$o->skill_type = $sk[1];
		$o->required_level = 0;
		$o->date_validite = 1;
		$o->temps_theorique = 0;
		$o->description = 'Năng lực '.$sk[0];
		if ($o->create($user) <= 0) {
			fail('skill', $o);
			continue;
		}
		$skills[] = $o->id;
		created('Kỹ năng');
	}
	$jobids = array();
	foreach ($DEPARTMENTS as $dept => $jobs) {
		foreach (array_unique($jobs) as $jl) {
			$j = new Job($db);
			$j->label = $jl;
			$j->description = 'Vị trí thuộc phòng '.$dept;
			$j->deplacement = ($dept == 'Kinh doanh' || $dept == 'Kỹ thuật') ? 1 : 0;
			if ($j->create($user) <= 0) {
				fail('job', $j);
				continue;
			}
			$jobids[$jl] = $j->id;
			created('Hồ sơ công việc');
		}
	}
	foreach ($employees as $uid) {
		$u = new User($db);
		$u->fetch($uid);
		if (empty($jobids[$u->job])) {
			continue;
		}
		$ps = new Position($db);
		$ps->fk_user = $uid;
		$ps->fk_job = $jobids[$u->job];
		$ps->date_start = $u->dateemployment;
		$ps->description = 'Bổ nhiệm vị trí '.$u->job;
		if ($ps->create($user) <= 0) {
			fail('position', $ps);
			continue;
		}
		created('Vị trí nhân viên');
	}

	if (isModEnabled('holiday')) {
		require_once DOL_DOCUMENT_ROOT.'/holiday/class/holiday.class.php';
		$types = array();
		$resql = $db->query("SELECT rowid FROM ".MAIN_DB_PREFIX."c_holiday_types WHERE active = 1 AND code IN ('LEAVE_SICK','LEAVE_OTHER','LEAVE_PAID','LEAVE_PAID_FR')");
		while ($resql && ($obj = $db->fetch_object($resql))) {
			$types[] = (int) $obj->rowid;
		}
		$REASONS = array('Nghỉ phép năm', 'Nghỉ ốm', 'Việc gia đình', 'Về quê', 'Đi khám sức khỏe', 'Nghỉ cưới');
		$nb = (int) round(70 * $scale);
		for ($i = 0; $i < $nb && $types && $employees; $i++) {
			$uid = pick($employees);
			$start = dol_get_first_hour(between($now - 240 * $DAY, $now + 45 * $DAY));
			$len = pick(array(0, 0, 1, 1, 2, 4));
			$h = new Holiday($db);
			$h->fk_user = $uid;
			$h->date_debut = $start;
			$h->date_fin = $start + $len * $DAY;
			$h->halfday = 0;
			$h->fk_type = pick($types);
			$h->fk_validator = $user->id;
			$h->description = pick($REASONS);
			if ($h->create($user) <= 0) {
				fail('leave', $h);
				continue;
			}
			created('Đơn nghỉ phép');
			$r = mt_rand(1, 10);
			if ($r >= 3) {
				$h->fetch($h->id);
				$h->validate($user);
				if ($r >= 5 && $start < $now) {
					$h->fetch($h->id);
					$h->approve($user);
				}
			}
		}
	}

	if (isModEnabled('recruitment')) {
		require_once DOL_DOCUMENT_ROOT.'/recruitment/class/recruitmentjobposition.class.php';
		require_once DOL_DOCUMENT_ROOT.'/recruitment/class/recruitmentcandidature.class.php';
		$POSTS = array(array('Nhân viên kinh doanh thiết bị công nghiệp', 3, 15000000), array('Kỹ sư tự động hóa', 2, 22000000), array('Kế toán kho', 1, 12000000),
			array('Nhân viên giao nhận', 2, 9000000), array('Trưởng nhóm kinh doanh khu vực phía Bắc', 1, 30000000), array('Thực tập sinh kỹ thuật', 4, 5000000));
		foreach ($POSTS as $k => $po) {
			$jp = new RecruitmentJobPosition($db);
			$jp->ref = '(PROV'.($k + 1).uniqid().')';
			$jp->label = $po[0];
			$jp->qty = $po[1];
			$jp->fk_user_recruiter = pick($allusers);
			$jp->fk_user_supervisor = pick($allusers);
			$jp->date_planned = $now + mt_rand(10, 60) * $DAY;
			$jp->remuneration_suggested = number_format($po[2], 0, ',', '.').' VNĐ';
			$jp->description = 'Mô tả công việc: '.$po[0].'. Yêu cầu tối thiểu 1 năm kinh nghiệm, ưu tiên ứng viên có kiến thức thiết bị công nghiệp.';
			$jp->status = 0;
			if ($jp->create($user) <= 0) {
				fail('job position', $jp);
				continue;
			}
			$jp->validate($user);
			created('Vị trí tuyển dụng');
			$nbc = (int) round(mt_rand(3, 9) * $scale);
			for ($c = 0; $c < $nbc; $c++) {
				list($ln, $fn, $gender) = person();
				$ca = new RecruitmentCandidature($db);
				$ca->ref = '(PROV'.uniqid().')';
				$ca->fk_recruitmentjobposition = $jp->id;
				$ca->lastname = $ln;
				$ca->firstname = $fn;
				$ca->email = slug(lastword($fn)).slug($ln).mt_rand(1, 999).'@gmail.com';
				$ca->phone = phone();
				$ca->date_birth = $now - mt_rand(21, 40) * 365 * $DAY;
				$ca->remuneration_requested = vnd($po[2] * mt_rand(85, 130) / 100);
				$ca->description = 'Ứng tuyển qua '.pick(array('TopCV', 'VietnamWorks', 'LinkedIn', 'giới thiệu nội bộ'));
				$ca->status = 0;
				if ($ca->create($user) <= 0) {
					fail('candidature', $ca);
					continue;
				}
				if (chance(0.8)) {
					$ca->validate($user);
				}
				created('Hồ sơ ứng viên');
			}
		}
	}
}

/*
 * 12. Donations (Tài trợ)
 */
if (isModEnabled('don')) {
	section('Donations');
	require_once DOL_DOCUMENT_ROOT.'/don/class/don.class.php';
	$nb = (int) round(15 * $scale);
	for ($i = 0; $i < $nb; $i++) {
		list($ln, $fn) = person();
		$dn = new Don($db);
		$dn->date = between($now - 300 * $DAY, $now);
		$dn->amount = vnd(mt_rand(1, 50) * 1000000);
		$dn->firstname = $fn;
		$dn->lastname = $ln;
		$dn->societe = chance(0.5) ? pick($CO_PREFIX).' '.pick($CO_TRADE).' '.pick($CO_NAME) : '';
		$dn->country_id = $countryid;
		$dn->email = 'donor'.mt_rand(1, 999).'@gmail.com';
		$dn->public = 1;
		$dn->modepaymentid = 2;
		$dn->note_public = 'Tài trợ quỹ khuyến học';
		if ($dn->create($user) <= 0) {
			fail('donation', $dn);
			continue;
		}
		if (chance(0.7)) {
			$dn->setValid($user);
		}
		created('Tài trợ');
	}
}

/*
 * Summary
 */
echo "\n=========================================\n";
echo "Demo data created:\n";
foreach ($stats as $k => $v) {
	printf("  %-28s %6d\n", $k, $v);
}
echo "Errors: ".$errors."\n";
echo "Demo employees can log in with password: Demo@2026\n";
exit($errors ? 2 : 0);
