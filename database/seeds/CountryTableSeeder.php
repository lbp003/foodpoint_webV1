<?php

use Illuminate\Database\Seeder;

class CountryTableSeeder extends Seeder {
	/**
	 * Run the database seeds.
	 *
	 * @return void
	 */
	public function run() {
		DB::table('country')->delete();

		DB::table('country')->insert([
			['code' => 'AF', 'name' => 'Afghanistan', 'iso3' => 'AFG', 'phone_code' => '4', 'phone_code' => '93', 'status' => '1','stripe_country' =>'0'],['code' => 'AL', 'name' => 'Albania', 'iso3' => 'ALB', 'phone_code' => '8', 'phone_code' => '355', 'status' => '1','stripe_country' =>'0'],
			['code' => 'DZ', 'name' => 'Algeria', 'iso3' => 'DZA', 'phone_code' => '12', 'phone_code' => '213', 'status' => '1','stripe_country' =>'0'],
			['code' => 'AS', 'name' => 'American Samoa', 'iso3' => 'ASM', 'phone_code' => '16', 'phone_code' => '1684', 'status' => '1','stripe_country' =>'0'],
			['code' => 'AD', 'name' => 'Andorra', 'iso3' => 'AND', 'phone_code' => '20', 'phone_code' => '376', 'status' => '1','stripe_country' =>'0'],
			['code' => 'AO', 'name' => 'Angola', 'iso3' => 'AGO', 'phone_code' => '24', 'phone_code' => '244', 'status' => '1','stripe_country' =>'0'],
			['code' => 'AI', 'name' => 'Anguilla', 'iso3' => 'AIA', 'phone_code' => '660', 'phone_code' => '1264', 'status' => '1','stripe_country' =>'0'],['code' => 'AQ', 'name' => 'Antarctica', 'iso3' => NULL, 'phone_code' => NULL, 'phone_code' => '0', 'status' => '1','stripe_country' =>'0'],
			['code' => 'AG', 'name' => 'Antigua and Barbuda', 'iso3' => 'ATG', 'phone_code' => '28', 'phone_code' => '1268', 'status' => '1','stripe_country' =>'0'],
			['code' => 'AR', 'name' => 'Argentina', 'iso3' => 'ARG', 'phone_code' => '32', 'phone_code' => '54', 'status' => '1','stripe_country' =>'0'],
			['code' => 'AM', 'name' => 'Armenia', 'iso3' => 'ARM', 'phone_code' => '51', 'phone_code' => '374', 'status' => '1','stripe_country' =>'0'],
			['code' => 'AW', 'name' => 'Aruba', 'iso3' => 'ABW', 'phone_code' => '533', 'phone_code' => '297', 'status' => '1','stripe_country' =>'0'],
			['code' => 'AU', 'name' => 'Australia', 'iso3' => 'AUS', 'phone_code' => '36', 'phone_code' => '61', 'status' => '1','stripe_country' =>'1'],
			['code' => 'AT', 'name' => 'Austria', 'iso3' => 'AUT', 'phone_code' => '40', 'phone_code' => '43', 'status' => '1','stripe_country' =>'1'],
			['code' => 'AZ', 'name' => 'Azerbaijan', 'iso3' => 'AZE', 'phone_code' => '31', 'phone_code' => '994', 'status' => '1','stripe_country' =>'0'],
			['code' => 'BS', 'name' => 'Bahamas', 'iso3' => 'BHS', 'phone_code' => '44', 'phone_code' => '1242', 'status' => '1','stripe_country' =>'0'],
			['code' => 'BH', 'name' => 'Bahrain', 'iso3' => 'BHR', 'phone_code' => '48', 'phone_code' => '973', 'status' => '1','stripe_country' =>'0'],
			['code' => 'BD', 'name' => 'Bangladesh', 'iso3' => 'BGD', 'phone_code' => '50', 'phone_code' => '880', 'status' => '1','stripe_country' =>'0'],
			['code' => 'BB', 'name' => 'Barbados', 'iso3' => 'BRB', 'phone_code' => '52', 'phone_code' => '1246', 'status' => '1','stripe_country' =>'0'],
			['code' => 'BY', 'name' => 'Belarus', 'iso3' => 'BLR', 'phone_code' => '112', 'phone_code' => '375', 'status' => '1','stripe_country' =>'0'],
			['code' => 'BE', 'name' => 'Belgium', 'iso3' => 'BEL', 'phone_code' => '56', 'phone_code' => '32', 'status' => '1','stripe_country' =>'1'],
			['code' => 'BZ', 'name' => 'Belize', 'iso3' => 'BLZ', 'phone_code' => '84', 'phone_code' => '501', 'status' => '1','stripe_country' =>'0'],
			['code' => 'BJ', 'name' => 'Benin', 'iso3' => 'BEN', 'phone_code' => '204', 'phone_code' => '229', 'status' => '1','stripe_country' =>'0'],
			['code' => 'BM', 'name' => 'Bermuda', 'iso3' => 'BMU', 'phone_code' => '60', 'phone_code' => '1441', 'status' => '1','stripe_country' =>'0'],
			['code' => 'BT', 'name' => 'Bhutan', 'iso3' => 'BTN', 'phone_code' => '64', 'phone_code' => '975', 'status' => '1','stripe_country' =>'0'],
			['code' => 'BO', 'name' => 'Bolivia', 'iso3' => 'BOL', 'phone_code' => '68', 'phone_code' => '591', 'status' => '1','stripe_country' =>'0'],
			['code' => 'BA', 'name' => 'Bosnia and Herzegovina', 'iso3' => 'BIH', 'phone_code' => '70', 'phone_code' => '387', 'status' => '1','stripe_country' =>'0'],
			['code' => 'BW', 'name' => 'Botswana', 'iso3' => 'BWA', 'phone_code' => '72', 'phone_code' => '267', 'status' => '1','stripe_country' =>'0'],
			['code' => 'BV', 'name' => 'Bouvet Island', 'iso3' => NULL, 'phone_code' => NULL, 'phone_code' => '0', 'status' => '1','stripe_country' =>'0'],
			['code' => 'BR', 'name' => 'Brazil', 'iso3' => 'BRA', 'phone_code' => '76', 'phone_code' => '55', 'status' => '1','stripe_country' =>'0'],
			['code' => 'IO', 'name' => 'British Indian Ocean Territory', 'iso3' => NULL, 'phone_code' => NULL, 'phone_code' => '246', 'status' => '1','stripe_country' =>'0'],
			['code' => 'BN', 'name' => 'Brunei Darussalam', 'iso3' => 'BRN', 'phone_code' => '96', 'phone_code' => '673', 'status' => '1','stripe_country' =>'0'],
			['code' => 'BG', 'name' => 'Bulgaria', 'iso3' => 'BGR', 'phone_code' => '100', 'phone_code' => '359', 'status' => '1','stripe_country' =>'0'],
			['code' => 'BF', 'name' => 'Burkina Faso', 'iso3' => 'BFA', 'phone_code' => '854', 'phone_code' => '226', 'status' => '1','stripe_country' =>'0'],
			['code' => 'BI', 'name' => 'Burundi', 'iso3' => 'BDI', 'phone_code' => '108', 'phone_code' => '257', 'status' => '1','stripe_country' =>'0'],
			['code' => 'KH', 'name' => 'Cambodia', 'iso3' => 'KHM', 'phone_code' => '116', 'phone_code' => '855', 'status' => '1','stripe_country' =>'0'],
			['code' => 'CM', 'name' => 'Cameroon', 'iso3' => 'CMR', 'phone_code' => '120', 'phone_code' => '237', 'status' => '1','stripe_country' =>'0'],
			['code' => 'CA', 'name' => 'Canada', 'iso3' => 'CAN', 'phone_code' => '124', 'phone_code' => '1', 'status' => '1','stripe_country' =>'1'],
			['code' => 'CV', 'name' => 'Cape Verde', 'iso3' => 'CPV', 'phone_code' => '132', 'phone_code' => '238', 'status' => '1','stripe_country' =>'0'],
			['code' => 'KY', 'name' => 'Cayman Islands', 'iso3' => 'CYM', 'phone_code' => '136', 'phone_code' => '1345', 'status' => '1','stripe_country' =>'0'],
			['code' => 'CF', 'name' => 'Central African Republic', 'iso3' => 'CAF', 'phone_code' => '140', 'phone_code' => '236', 'status' => '1','stripe_country' =>'0'],
			['code' => 'TD', 'name' => 'Chad', 'iso3' => 'TCD', 'phone_code' => '148', 'phone_code' => '235', 'status' => '1','stripe_country' =>'0'],
			['code' => 'CL', 'name' => 'Chile', 'iso3' => 'CHL', 'phone_code' => '152', 'phone_code' => '56', 'status' => '1','stripe_country' =>'0'],
			['code' => 'CN', 'name' => 'China', 'iso3' => 'CHN', 'phone_code' => '156', 'phone_code' => '86', 'status' => '1','stripe_country' =>'0'],
			['code' => 'CX', 'name' => 'Christmas Island', 'iso3' => NULL, 'phone_code' => NULL, 'phone_code' => '61', 'status' => '1','stripe_country' =>'0'],
			['code' => 'CC', 'name' => 'Cocos (Keeling) Islands', 'iso3' => NULL, 'phone_code' => NULL, 'phone_code' => '672', 'status' => '1','stripe_country' =>'0'],
			['code' => 'CO', 'name' => 'Colombia', 'iso3' => 'COL', 'phone_code' => '170', 'phone_code' => '57', 'status' => '1','stripe_country' =>'0'],
			['code' => 'KM', 'name' => 'Comoros', 'iso3' => 'COM', 'phone_code' => '174', 'phone_code' => '269', 'status' => '1','stripe_country' =>'0'],
			['code' => 'CG', 'name' => 'Congo', 'iso3' => 'COG', 'phone_code' => '178', 'phone_code' => '242', 'status' => '1','stripe_country' =>'0'],
			['code' => 'CD', 'name' => 'Congo, the Democratic Republic of the', 'iso3' => 'COD', 'phone_code' => '180', 'phone_code' => '242', 'status' => '1','stripe_country' =>'0'],
			['code' => 'CK', 'name' => 'Cook Islands', 'iso3' => 'COK', 'phone_code' => '184', 'phone_code' => '682', 'status' => '1','stripe_country' =>'0'],
			['code' => 'CR', 'name' => 'Costa Rica', 'iso3' => 'CRI', 'phone_code' => '188', 'phone_code' => '506', 'status' => '1','stripe_country' =>'0'],
			['code' => 'CI', 'name' => 'Cote D\'Ivoire', 'iso3' => 'CIV', 'phone_code' => '384', 'phone_code' => '225', 'status' => '1','stripe_country' =>'0'],
			['code' => 'HR', 'name' => 'Croatia', 'iso3' => 'HRV', 'phone_code' => '191', 'phone_code' => '385', 'status' => '1','stripe_country' =>'0'],
			['code' => 'CU', 'name' => 'Cuba', 'iso3' => 'CUB', 'phone_code' => '192', 'phone_code' => '53', 'status' => '1','stripe_country' =>'0'],
			['code' => 'CY', 'name' => 'Cyprus', 'iso3' => 'CYP', 'phone_code' => '196', 'phone_code' => '357', 'status' => '1','stripe_country' =>'0'],
			['code' => 'CZ', 'name' => 'Czech Republic', 'iso3' => 'CZE', 'phone_code' => '203', 'phone_code' => '420', 'status' => '1','stripe_country' =>'0'],
			['code' => 'DK', 'name' => 'Denmark', 'iso3' => 'DNK', 'phone_code' => '208', 'phone_code' => '45', 'status' => '1','stripe_country' =>'1'],
			['code' => 'DJ', 'name' => 'Djibouti', 'iso3' => 'DJI', 'phone_code' => '262', 'phone_code' => '253', 'status' => '1','stripe_country' =>'0'],
			['code' => 'DM', 'name' => 'Dominica', 'iso3' => 'DMA', 'phone_code' => '212', 'phone_code' => '1767', 'status' => '1','stripe_country' =>'0'],
			['code' => 'DO', 'name' => 'Dominican Republic', 'iso3' => 'DOM', 'phone_code' => '214', 'phone_code' => '1809', 'status' => '1','stripe_country' =>'0'],
			['code' => 'EC', 'name' => 'Ecuador', 'iso3' => 'ECU', 'phone_code' => '218', 'phone_code' => '593', 'status' => '1','stripe_country' =>'0'],
			['code' => 'EG', 'name' => 'Egypt', 'iso3' => 'EGY', 'phone_code' => '818', 'phone_code' => '20', 'status' => '1','stripe_country' =>'0'],
			['code' => 'SV', 'name' => 'El Salvador', 'iso3' => 'SLV', 'phone_code' => '222', 'phone_code' => '503', 'status' => '1','stripe_country' =>'0'],
			['code' => 'GQ', 'name' => 'Equatorial Guinea', 'iso3' => 'GNQ', 'phone_code' => '226', 'phone_code' => '240', 'status' => '1','stripe_country' =>'0'],
			['code' => 'ER', 'name' => 'Eritrea', 'iso3' => 'ERI', 'phone_code' => '232', 'phone_code' => '291', 'status' => '1','stripe_country' =>'0'],
			['code' => 'EE', 'name' => 'Estonia', 'iso3' => 'EST', 'phone_code' => '233', 'phone_code' => '372', 'status' => '1','stripe_country' =>'0'],
			['code' => 'ET', 'name' => 'Ethiopia', 'iso3' => 'ETH', 'phone_code' => '231', 'phone_code' => '251', 'status' => '1','stripe_country' =>'0'],
			['code' => 'FK', 'name' => 'Falkland Islands (Malvinas)', 'iso3' => 'FLK', 'phone_code' => '238', 'phone_code' => '500', 'status' => '1','stripe_country' =>'0'],
			['code' => 'FO', 'name' => 'Faroe Islands', 'iso3' => 'FRO', 'phone_code' => '234', 'phone_code' => '298', 'status' => '1','stripe_country' =>'0'],
			['code' => 'FJ', 'name' => 'Fiji', 'iso3' => 'FJI', 'phone_code' => '242', 'phone_code' => '679', 'status' => '1','stripe_country' =>'0'],
			['code' => 'FI', 'name' => 'Finland', 'iso3' => 'FIN', 'phone_code' => '246', 'phone_code' => '358', 'status' => '1','stripe_country' =>'1'],
			['code' => 'FR', 'name' => 'France', 'iso3' => 'FRA', 'phone_code' => '250', 'phone_code' => '33', 'status' => '1','stripe_country' =>'1'],
			['code' => 'GF', 'name' => 'French Guiana', 'iso3' => 'GUF', 'phone_code' => '254', 'phone_code' => '594', 'status' => '1','stripe_country' =>'0'],
			['code' => 'PF', 'name' => 'French Polynesia', 'iso3' => 'PYF', 'phone_code' => '258', 'phone_code' => '689', 'status' => '1','stripe_country' =>'0'],
			['code' => 'TF', 'name' => 'French Southern Territories', 'iso3' => NULL, 'phone_code' => NULL, 'phone_code' => '0', 'status' => '1','stripe_country' =>'0'],
			['code' => 'GA', 'name' => 'Gabon', 'iso3' => 'GAB', 'phone_code' => '266', 'phone_code' => '241', 'status' => '1','stripe_country' =>'0'],
			['code' => 'GM', 'name' => 'Gambia', 'iso3' => 'GMB', 'phone_code' => '270', 'phone_code' => '220', 'status' => '1','stripe_country' =>'0'],
			['code' => 'GE', 'name' => 'Georgia', 'iso3' => 'GEO', 'phone_code' => '268', 'phone_code' => '995', 'status' => '1','stripe_country' =>'0'],
			['code' => 'DE', 'name' => 'Germany', 'iso3' => 'DEU', 'phone_code' => '276', 'phone_code' => '49', 'status' => '1','stripe_country' =>'1'],
			['code' => 'GH', 'name' => 'Ghana', 'iso3' => 'GHA', 'phone_code' => '288', 'phone_code' => '233', 'status' => '1','stripe_country' =>'0'],
			['code' => 'GI', 'name' => 'Gibraltar', 'iso3' => 'GIB', 'phone_code' => '292', 'phone_code' => '350', 'status' => '1','stripe_country' =>'0'],
			['code' => 'GR', 'name' => 'Greece', 'iso3' => 'GRC', 'phone_code' => '300', 'phone_code' => '30', 'status' => '1','stripe_country' =>'0'],
			['code' => 'GL', 'name' => 'Greenland', 'iso3' => 'GRL', 'phone_code' => '304', 'phone_code' => '299', 'status' => '1','stripe_country' =>'0'],
			['code' => 'GD', 'name' => 'Grenada', 'iso3' => 'GRD', 'phone_code' => '308', 'phone_code' => '1473', 'status' => '1','stripe_country' =>'0'],
			['code' => 'GP', 'name' => 'Guadeloupe', 'iso3' => 'GLP', 'phone_code' => '312', 'phone_code' => '590', 'status' => '1','stripe_country' =>'0'],
			['code' => 'GU', 'name' => 'Guam', 'iso3' => 'GUM', 'phone_code' => '316', 'phone_code' => '1671', 'status' => '1','stripe_country' =>'0'],
			['code' => 'GT', 'name' => 'Guatemala', 'iso3' => 'GTM', 'phone_code' => '320', 'phone_code' => '502', 'status' => '1','stripe_country' =>'0'],
			['code' => 'GN', 'name' => 'Guinea', 'iso3' => 'GIN', 'phone_code' => '324', 'phone_code' => '224', 'status' => '1','stripe_country' =>'0'],
			['code' => 'GW', 'name' => 'Guinea-Bissau', 'iso3' => 'GNB', 'phone_code' => '624', 'phone_code' => '245', 'status' => '1','stripe_country' =>'0'],
			['code' => 'GY', 'name' => 'Guyana', 'iso3' => 'GUY', 'phone_code' => '328', 'phone_code' => '592', 'status' => '1','stripe_country' =>'0'],
			['code' => 'HT', 'name' => 'Haiti', 'iso3' => 'HTI', 'phone_code' => '332', 'phone_code' => '509', 'status' => '1','stripe_country' =>'0'],
			['code' => 'HM', 'name' => 'Heard Island and Mcdonald Islands', 'iso3' => NULL, 'phone_code' => NULL, 'phone_code' => '0', 'status' => '1','stripe_country' =>'0','stripe_country' =>'0'],
			['code' => 'VA', 'name' => 'Holy See (Vatican City State)', 'iso3' => 'VAT', 'phone_code' => '336', 'phone_code' => '39', 'status' => '1','stripe_country' =>'0'],
			['code' => 'HN', 'name' => 'Honduras', 'iso3' => 'HND', 'phone_code' => '340', 'phone_code' => '504', 'status' => '1','stripe_country' =>'0'],['code' => 'HK', 'name' => 'Hong Kong', 'iso3' => 'HKG', 'phone_code' => '344', 'phone_code' => '852', 'status' => '1','stripe_country' =>'1'],
			['code' => 'HU', 'name' => 'Hungary', 'iso3' => 'HUN', 'phone_code' => '348', 'phone_code' => '36', 'status' => '1','stripe_country' =>'0'],
			['code' => 'IS', 'name' => 'Iceland', 'iso3' => 'ISL', 'phone_code' => '352', 'phone_code' => '354', 'status' => '1','stripe_country' =>'0'],
			['code' => 'IN', 'name' => 'India', 'iso3' => 'IND', 'phone_code' => '356', 'phone_code' => '91', 'status' => '1','stripe_country' =>'0'],
			['code' => 'ID', 'name' => 'Indonesia', 'iso3' => 'IDN', 'phone_code' => '360', 'phone_code' => '62', 'status' => '1','stripe_country' =>'0'],
			['code' => 'IR', 'name' => 'Iran, Islamic Republic of', 'iso3' => 'IRN', 'phone_code' => '364', 'phone_code' => '98', 'status' => '1','stripe_country' =>'0'],
			['code' => 'IQ', 'name' => 'Iraq', 'iso3' => 'IRQ', 'phone_code' => '368', 'phone_code' => '964', 'status' => '1','stripe_country' =>'0'],
			['code' => 'IE', 'name' => 'Ireland', 'iso3' => 'IRL', 'phone_code' => '372', 'phone_code' => '353', 'status' => '1','stripe_country' =>'1'],
			['code' => 'IL', 'name' => 'Israel', 'iso3' => 'ISR', 'phone_code' => '376', 'phone_code' => '972', 'status' => '1','stripe_country' =>'0'],
			['code' => 'IT', 'name' => 'Italy', 'iso3' => 'ITA', 'phone_code' => '380', 'phone_code' => '39', 'status' => '1','stripe_country' =>'1'],
			['code' => 'JM', 'name' => 'Jamaica', 'iso3' => 'JAM', 'phone_code' => '388', 'phone_code' => '1876', 'status' => '1','stripe_country' =>'0'],
			['code' => 'JP', 'name' => 'Japan', 'iso3' => 'JPN', 'phone_code' => '392', 'phone_code' => '81', 'status' => '1','stripe_country' =>'1'],
			['code' => 'JO', 'name' => 'Jordan', 'iso3' => 'JOR', 'phone_code' => '400', 'phone_code' => '962', 'status' => '1','stripe_country' =>'0'],
			['code' => 'KZ', 'name' => 'Kazakhstan', 'iso3' => 'KAZ', 'phone_code' => '398', 'phone_code' => '7', 'status' => '1','stripe_country' =>'0'],
			['code' => 'KE', 'name' => 'Kenya', 'iso3' => 'KEN', 'phone_code' => '404', 'phone_code' => '254', 'status' => '1','stripe_country' =>'0'],
			['code' => 'KI', 'name' => 'Kiribati', 'iso3' => 'KIR', 'phone_code' => '296', 'phone_code' => '686', 'status' => '1','stripe_country' =>'0'],
			['code' => 'KP', 'name' => 'Korea, Democratic People\'s Republic of', 'iso3' => 'PRK', 'phone_code' => '408', 'phone_code' => '850', 'status' => '1','stripe_country' =>'0'],
			['code' => 'KR', 'name' => 'Korea, Republic of', 'iso3' => 'KOR', 'phone_code' => '410', 'phone_code' => '82', 'status' => '1','stripe_country' =>'0'],
			['code' => 'KW', 'name' => 'Kuwait', 'iso3' => 'KWT', 'phone_code' => '414', 'phone_code' => '965', 'status' => '1','stripe_country' =>'0'],
			['code' => 'KG', 'name' => 'Kyrgyzstan', 'iso3' => 'KGZ', 'phone_code' => '417', 'phone_code' => '996', 'status' => '1','stripe_country' =>'0'],
			['code' => 'LA', 'name' => 'Lao People\'s Democratic Republic', 'iso3' => 'LAO', 'phone_code' => '418', 'phone_code' => '856', 'status' => '1','stripe_country' =>'0'],
			['code' => 'LV', 'name' => 'Latvia', 'iso3' => 'LVA', 'phone_code' => '428', 'phone_code' => '371', 'status' => '1','stripe_country' =>'0'],
			['code' => 'LB', 'name' => 'Lebanon', 'iso3' => 'LBN', 'phone_code' => '422', 'phone_code' => '961', 'status' => '1','stripe_country' =>'0'],
			['code' => 'LS', 'name' => 'Lesotho', 'iso3' => 'LSO', 'phone_code' => '426', 'phone_code' => '266', 'status' => '1','stripe_country' =>'0'],
			['code' => 'LR', 'name' => 'Liberia', 'iso3' => 'LBR', 'phone_code' => '430', 'phone_code' => '231', 'status' => '1','stripe_country' =>'0'],
			['code' => 'LY', 'name' => 'Libyan Arab Jamahiriya', 'iso3' => 'LBY', 'phone_code' => '434', 'phone_code' => '218', 'status' => '1','stripe_country' =>'0'],
			['code' => 'LI', 'name' => 'Liechtenstein', 'iso3' => 'LIE', 'phone_code' => '438', 'phone_code' => '423', 'status' => '1','stripe_country' =>'0'],
			['code' => 'LT', 'name' => 'Lithuania', 'iso3' => 'LTU', 'phone_code' => '440', 'phone_code' => '370', 'status' => '1','stripe_country' =>'0'],
			['code' => 'LU', 'name' => 'Luxembourg', 'iso3' => 'LUX', 'phone_code' => '442', 'phone_code' => '352', 'status' => '1','stripe_country' =>'1'],
			['code' => 'MO', 'name' => 'Macao', 'iso3' => 'MAC', 'phone_code' => '446', 'phone_code' => '853', 'status' => '1','stripe_country' =>'0'],
			['code' => 'MK', 'name' => 'Macedonia, the Former Yugoslav Republic of', 'iso3' => 'MKD', 'phone_code' => '807', 'phone_code' => '389', 'status' => '1','stripe_country' =>'0'],
			['code' => 'MG', 'name' => 'Madagascar', 'iso3' => 'MDG', 'phone_code' => '450', 'phone_code' => '261', 'status' => '1','stripe_country' =>'0'],
			['code' => 'MW', 'name' => 'Malawi', 'iso3' => 'MWI', 'phone_code' => '454', 'phone_code' => '265', 'status' => '1','stripe_country' =>'0'],
			['code' => 'MY', 'name' => 'Malaysia', 'iso3' => 'MYS', 'phone_code' => '458', 'phone_code' => '60', 'status' => '1','stripe_country' =>'0'],
			['code' => 'MV', 'name' => 'Maldives', 'iso3' => 'MDV', 'phone_code' => '462', 'phone_code' => '960', 'status' => '1','stripe_country' =>'0'],
			['code' => 'ML', 'name' => 'Mali', 'iso3' => 'MLI', 'phone_code' => '466', 'phone_code' => '223', 'status' => '1','stripe_country' =>'0'],
			['code' => 'MT', 'name' => 'Malta', 'iso3' => 'MLT', 'phone_code' => '470', 'phone_code' => '356', 'status' => '1','stripe_country' =>'0'],
			['code' => 'MH', 'name' => 'Marshall Islands', 'iso3' => 'MHL', 'phone_code' => '584', 'phone_code' => '692', 'status' => '1','stripe_country' =>'0'],['code' => 'MQ', 'name' => 'Martinique', 'iso3' => 'MTQ', 'phone_code' => '474', 'phone_code' => '596', 'status' => '1','stripe_country' =>'0'],
			['code' => 'MR', 'name' => 'Mauritania', 'iso3' => 'MRT', 'phone_code' => '478', 'phone_code' => '222', 'status' => '1','stripe_country' =>'0'],
			['code' => 'MU', 'name' => 'Mauritius', 'iso3' => 'MUS', 'phone_code' => '480', 'phone_code' => '230', 'status' => '1','stripe_country' =>'0'],
			['code' => 'YT', 'name' => 'Mayotte', 'iso3' => NULL, 'phone_code' => NULL, 'phone_code' => '269', 'status' => '1','stripe_country' =>'0'],
			['code' => 'MX', 'name' => 'Mexico', 'iso3' => 'MEX', 'phone_code' => '484', 'phone_code' => '52', 'status' => '1','stripe_country' =>'0'],
			['code' => 'FM', 'name' => 'Micronesia, Federated States of', 'iso3' => 'FSM', 'phone_code' => '583', 'phone_code' => '691', 'status' => '1','stripe_country' =>'0'],
			['code' => 'MD', 'name' => 'Moldova, Republic of', 'iso3' => 'MDA', 'phone_code' => '498', 'phone_code' => '373', 'status' => '1','stripe_country' =>'0'],
			['code' => 'MC', 'name' => 'Monaco', 'iso3' => 'MCO', 'phone_code' => '492', 'phone_code' => '377', 'status' => '1','stripe_country' =>'0'],
			['code' => 'MN', 'name' => 'Mongolia', 'iso3' => 'MNG', 'phone_code' => '496', 'phone_code' => '976', 'status' => '1','stripe_country' =>'0'],
			['code' => 'MS', 'name' => 'Montserrat', 'iso3' => 'MSR', 'phone_code' => '500', 'phone_code' => '1664', 'status' => '1','stripe_country' =>'0'],
			['code' => 'MA', 'name' => 'Morocco', 'iso3' => 'MAR', 'phone_code' => '504', 'phone_code' => '212', 'status' => '1','stripe_country' =>'0'],
			['code' => 'MZ', 'name' => 'Mozambique', 'iso3' => 'MOZ', 'phone_code' => '508', 'phone_code' => '258', 'status' => '1','stripe_country' =>'0'],
			['code' => 'MM', 'name' => 'Myanmar', 'iso3' => 'MMR', 'phone_code' => '104', 'phone_code' => '95', 'status' => '1','stripe_country' =>'0'],
			['code' => 'NA', 'name' => 'Namibia', 'iso3' => 'NAM', 'phone_code' => '516', 'phone_code' => '264', 'status' => '1','stripe_country' =>'0'],
			['code' => 'NR', 'name' => 'Nauru', 'iso3' => 'NRU', 'phone_code' => '520', 'phone_code' => '674', 'status' => '1','stripe_country' =>'0'],
			['code' => 'NP', 'name' => 'Nepal', 'iso3' => 'NPL', 'phone_code' => '524', 'phone_code' => '977', 'status' => '1','stripe_country' =>'0'],
			['code' => 'NL', 'name' => 'Netherlands', 'iso3' => 'NLD', 'phone_code' => '528', 'phone_code' => '31', 'status' => '1','stripe_country' =>'1'],
			['code' => 'AN', 'name' => 'Netherlands Antilles', 'iso3' => 'ANT', 'phone_code' => '530', 'phone_code' => '599', 'status' => '1','stripe_country' =>'0'],
			['code' => 'NC', 'name' => 'New Caledonia', 'iso3' => 'NCL', 'phone_code' => '540', 'phone_code' => '687', 'status' => '1','stripe_country' =>'0'],
			['code' => 'NZ', 'name' => 'New Zealand', 'iso3' => 'NZL', 'phone_code' => '554', 'phone_code' => '64', 'status' => '1','stripe_country' =>'1'],
			['code' => 'NI', 'name' => 'Nicaragua', 'iso3' => 'NIC', 'phone_code' => '558', 'phone_code' => '505', 'status' => '1','stripe_country' =>'0'],
			['code' => 'NE', 'name' => 'Niger', 'iso3' => 'NER', 'phone_code' => '562', 'phone_code' => '227', 'status' => '1','stripe_country' =>'0'],
			['code' => 'NG', 'name' => 'Nigeria', 'iso3' => 'NGA', 'phone_code' => '566', 'phone_code' => '234', 'status' => '1','stripe_country' =>'0'],
			['code' => 'NU', 'name' => 'Niue', 'iso3' => 'NIU', 'phone_code' => '570', 'phone_code' => '683', 'status' => '1','stripe_country' =>'0'],
			['code' => 'NF', 'name' => 'Norfolk Island', 'iso3' => 'NFK', 'phone_code' => '574', 'phone_code' => '672', 'status' => '1','stripe_country' =>'0'],
			['code' => 'MP', 'name' => 'Northern Mariana Islands', 'iso3' => 'MNP', 'phone_code' => '580', 'phone_code' => '1670', 'status' => '1','stripe_country' =>'0'],
			['code' => 'NO', 'name' => 'Norway', 'iso3' => 'NOR', 'phone_code' => '578', 'phone_code' => '47', 'status' => '1','stripe_country' =>'1'],
			['code' => 'OM', 'name' => 'Oman', 'iso3' => 'OMN', 'phone_code' => '512', 'phone_code' => '968', 'status' => '1','stripe_country' =>'0'],
			['code' => 'PK', 'name' => 'Pakistan', 'iso3' => 'PAK', 'phone_code' => '586', 'phone_code' => '92', 'status' => '1','stripe_country' =>'0'],
			['code' => 'PW', 'name' => 'Palau', 'iso3' => 'PLW', 'phone_code' => '585', 'phone_code' => '680', 'status' => '1','stripe_country' =>'0'],
			['code' => 'PS', 'name' => 'Palestinian Territory, Occupied', 'iso3' => NULL, 'phone_code' => NULL, 'phone_code' => '970', 'status' => '1','stripe_country' =>'0'],
			['code' => 'PA', 'name' => 'Panama', 'iso3' => 'PAN', 'phone_code' => '591', 'phone_code' => '507', 'status' => '1','stripe_country' =>'0'],
			['code' => 'PG', 'name' => 'Papua New Guinea', 'iso3' => 'PNG', 'phone_code' => '598', 'phone_code' => '675', 'status' => '1','stripe_country' =>'0'],
			['code' => 'PY', 'name' => 'Paraguay', 'iso3' => 'PRY', 'phone_code' => '600', 'phone_code' => '595', 'status' => '1','stripe_country' =>'0'],
			['code' => 'PE', 'name' => 'Peru', 'iso3' => 'PER', 'phone_code' => '604', 'phone_code' => '51', 'status' => '1','stripe_country' =>'0'],
			['code' => 'PH', 'name' => 'Philippines', 'iso3' => 'PHL', 'phone_code' => '608', 'phone_code' => '63', 'status' => '1','stripe_country' =>'0'],
			['code' => 'PN', 'name' => 'Pitcairn', 'iso3' => 'PCN', 'phone_code' => '612', 'phone_code' => '0', 'status' => '1','stripe_country' =>'0'],
			['code' => 'PL', 'name' => 'Poland', 'iso3' => 'POL', 'phone_code' => '616', 'phone_code' => '48', 'status' => '1','stripe_country' =>'0'],
			['code' => 'PT', 'name' => 'Portugal', 'iso3' => 'PRT', 'phone_code' => '620', 'phone_code' => '351', 'status' => '1','stripe_country' =>'1'],
			['code' => 'PR', 'name' => 'Puerto Rico', 'iso3' => 'PRI', 'phone_code' => '630', 'phone_code' => '1787', 'status' => '1','stripe_country' =>'0'],
			['code' => 'QA', 'name' => 'Qatar', 'iso3' => 'QAT', 'phone_code' => '634', 'phone_code' => '974', 'status' => '1','stripe_country' =>'0'],
			['code' => 'RE', 'name' => 'Reunion', 'iso3' => 'REU', 'phone_code' => '638', 'phone_code' => '262', 'status' => '1','stripe_country' =>'0'],
			['code' => 'RO', 'name' => 'Romania', 'iso3' => 'ROM', 'phone_code' => '642', 'phone_code' => '40', 'status' => '1','stripe_country' =>'0'],
			['code' => 'RU', 'name' => 'Russian Federation', 'iso3' => 'RUS', 'phone_code' => '643', 'phone_code' => '70', 'status' => '1','stripe_country' =>'0'],
			['code' => 'RW', 'name' => 'Rwanda', 'iso3' => 'RWA', 'phone_code' => '646', 'phone_code' => '250', 'status' => '1','stripe_country' =>'0'],
			['code' => 'SH', 'name' => 'Saint Helena', 'iso3' => 'SHN', 'phone_code' => '654', 'phone_code' => '290', 'status' => '1','stripe_country' =>'0'],
			['code' => 'KN', 'name' => 'Saint Kitts and Nevis', 'iso3' => 'KNA', 'phone_code' => '659', 'phone_code' => '1869', 'status' => '1','stripe_country' =>'0'],
			['code' => 'LC', 'name' => 'Saint Lucia', 'iso3' => 'LCA', 'phone_code' => '662', 'phone_code' => '1758', 'status' => '1','stripe_country' =>'0'],
			['code' => 'PM', 'name' => 'Saint Pierre and Miquelon', 'iso3' => 'SPM', 'phone_code' => '666', 'phone_code' => '508', 'status' => '1','stripe_country' =>'0'],
			['code' => 'VC', 'name' => 'Saint Vincent and the Grenadines', 'iso3' => 'VCT', 'phone_code' => '670', 'phone_code' => '1784', 'status' => '1','stripe_country' =>'0'],
			['code' => 'WS', 'name' => 'Samoa', 'iso3' => 'WSM', 'phone_code' => '882', 'phone_code' => '684', 'status' => '1','stripe_country' =>'0'],
			['code' => 'SM', 'name' => 'San Marino', 'iso3' => 'SMR', 'phone_code' => '674', 'phone_code' => '378', 'status' => '1','stripe_country' =>'0'],
			['code' => 'ST', 'name' => 'Sao Tome and Principe', 'iso3' => 'STP', 'phone_code' => '678', 'phone_code' => '239', 'status' => '1','stripe_country' =>'0'],
			['code' => 'SA', 'name' => 'Saudi Arabia', 'iso3' => 'SAU', 'phone_code' => '682', 'phone_code' => '966', 'status' => '1','stripe_country' =>'0'],
			['code' => 'SN', 'name' => 'Senegal', 'iso3' => 'SEN', 'phone_code' => '686', 'phone_code' => '221', 'status' => '1','stripe_country' =>'0'],['code' => 'RS', 'name' => 'Serbia and Montenegro', 'iso3' => NULL, 'phone_code' => NULL, 'phone_code' => '381', 'status' => '1','stripe_country' =>'0'],
			['code' => 'SC', 'name' => 'Seychelles', 'iso3' => 'SYC', 'phone_code' => '690', 'phone_code' => '248', 'status' => '1','stripe_country' =>'0'],
			['code' => 'SL', 'name' => 'Sierra Leone', 'iso3' => 'SLE', 'phone_code' => '694', 'phone_code' => '232', 'status' => '1','stripe_country' =>'0'],
			['code' => 'SG', 'name' => 'Singapore', 'iso3' => 'SGP', 'phone_code' => '702', 'phone_code' => '65', 'status' => '1','stripe_country' =>'1'],
			['code' => 'SK', 'name' => 'Slovakia', 'iso3' => 'SVK', 'phone_code' => '703', 'phone_code' => '421', 'status' => '1','stripe_country' =>'0'],
			['code' => 'SI', 'name' => 'Slovenia', 'iso3' => 'SVN', 'phone_code' => '705', 'phone_code' => '386', 'status' => '1','stripe_country' =>'0'],
			['code' => 'SB', 'name' => 'Solomon Islands', 'iso3' => 'SLB', 'phone_code' => '90', 'phone_code' => '677', 'status' => '1','stripe_country' =>'0'],
			['code' => 'SO', 'name' => 'Somalia', 'iso3' => 'SOM', 'phone_code' => '706', 'phone_code' => '252', 'status' => '1','stripe_country' =>'0'],
			['code' => 'ZA', 'name' => 'South Africa', 'iso3' => 'ZAF', 'phone_code' => '710', 'phone_code' => '27', 'status' => '1','stripe_country' =>'0'],
			['code' => 'GS', 'name' => 'South Georgia and the South Sandwich Islands', 'iso3' => NULL, 'phone_code' => NULL, 'phone_code' => '0', 'status' => '1','stripe_country' =>'0'],
			['code' => 'ES', 'name' => 'Spain', 'iso3' => 'ESP', 'phone_code' => '724', 'phone_code' => '34', 'status' => '1','stripe_country' =>'1'],
			['code' => 'LK', 'name' => 'Sri Lanka', 'iso3' => 'LKA', 'phone_code' => '144', 'phone_code' => '94', 'status' => '1','stripe_country' =>'0'],
			['code' => 'SD', 'name' => 'Sudan', 'iso3' => 'SDN', 'phone_code' => '736', 'phone_code' => '249', 'status' => '1','stripe_country' =>'0'],
			['code' => 'SR', 'name' => 'Suriname', 'iso3' => 'SUR', 'phone_code' => '740', 'phone_code' => '597', 'status' => '1','stripe_country' =>'0'],
			['code' => 'SJ', 'name' => 'Svalbard and Jan Mayen', 'iso3' => 'SJM', 'phone_code' => '744', 'phone_code' => '47', 'status' => '1','stripe_country' =>'0'],
			['code' => 'SZ', 'name' => 'Swaziland', 'iso3' => 'SWZ', 'phone_code' => '748', 'phone_code' => '268', 'status' => '1','stripe_country' =>'0'],
			['code' => 'SE', 'name' => 'Sweden', 'iso3' => 'SWE', 'phone_code' => '752', 'phone_code' => '46', 'status' => '1','stripe_country' =>'1'],
			['code' => 'CH', 'name' => 'Switzerland', 'iso3' => 'CHE', 'phone_code' => '756', 'phone_code' => '41', 'status' => '1','stripe_country' =>'1'],
			['code' => 'SY', 'name' => 'Syrian Arab Republic', 'iso3' => 'SYR', 'phone_code' => '760', 'phone_code' => '963', 'status' => '1','stripe_country' =>'0'],
			['code' => 'TW', 'name' => 'Taiwan, Province of China', 'iso3' => 'TWN', 'phone_code' => '158', 'phone_code' => '886', 'status' => '1','stripe_country' =>'0'],
			['code' => 'TJ', 'name' => 'Tajikistan', 'iso3' => 'TJK', 'phone_code' => '762', 'phone_code' => '992', 'status' => '1','stripe_country' =>'0'],
			['code' => 'TZ', 'name' => 'Tanzania, United Republic of', 'iso3' => 'TZA', 'phone_code' => '834', 'phone_code' => '255', 'status' => '1','stripe_country' =>'0'],
			['code' => 'TH', 'name' => 'Thailand', 'iso3' => 'THA', 'phone_code' => '764', 'phone_code' => '66', 'status' => '1','stripe_country' =>'0'],
			['code' => 'TL', 'name' => 'Timor-Leste', 'iso3' => NULL, 'phone_code' => NULL, 'phone_code' => '670', 'status' => '1','stripe_country' =>'0'],
			['code' => 'TG', 'name' => 'Togo', 'iso3' => 'TGO', 'phone_code' => '768', 'phone_code' => '228', 'status' => '1','stripe_country' =>'0'],
			['code' => 'TK', 'name' => 'Tokelau', 'iso3' => 'TKL', 'phone_code' => '772', 'phone_code' => '690', 'status' => '1','stripe_country' =>'0'],
			['code' => 'TO', 'name' => 'Tonga', 'iso3' => 'TON', 'phone_code' => '776', 'phone_code' => '676', 'status' => '1','stripe_country' =>'0'],
			['code' => 'TT', 'name' => 'Trinidad and Tobago', 'iso3' => 'TTO', 'phone_code' => '780', 'phone_code' => '1868', 'status' => '1','stripe_country' =>'0'],
			['code' => 'TN', 'name' => 'Tunisia', 'iso3' => 'TUN', 'phone_code' => '788', 'phone_code' => '216', 'status' => '1','stripe_country' =>'0'],
			['code' => 'TR', 'name' => 'Turkey', 'iso3' => 'TUR', 'phone_code' => '792', 'phone_code' => '90', 'status' => '1','stripe_country' =>'0'],
			['code' => 'TM', 'name' => 'Turkmenistan', 'iso3' => 'TKM', 'phone_code' => '795', 'phone_code' => '7370', 'status' => '1','stripe_country' =>'0'],
			['code' => 'TC', 'name' => 'Turks and Caicos Islands', 'iso3' => 'TCA', 'phone_code' => '796', 'phone_code' => '1649', 'status' => '1','stripe_country' =>'0'],
			['code' => 'TV', 'name' => 'Tuvalu', 'iso3' => 'TUV', 'phone_code' => '798', 'phone_code' => '688', 'status' => '1','stripe_country' =>'0'],
			['code' => 'UG', 'name' => 'Uganda', 'iso3' => 'UGA', 'phone_code' => '800', 'phone_code' => '256', 'status' => '1','stripe_country' =>'0'],
			['code' => 'UA', 'name' => 'Ukraine', 'iso3' => 'UKR', 'phone_code' => '804', 'phone_code' => '380', 'status' => '1','stripe_country' =>'0'],
			['code' => 'AE', 'name' => 'United Arab Emirates', 'iso3' => 'ARE', 'phone_code' => '784', 'phone_code' => '971', 'status' => '1','stripe_country' =>'0'],
			['code' => 'GB', 'name' => 'United Kingdom', 'iso3' => 'GBR', 'phone_code' => '826', 'phone_code' => '44', 'status' => '1','stripe_country' =>'1'],
			['code' => 'US', 'name' => 'United States', 'iso3' => 'USA', 'phone_code' => '840', 'phone_code' => '1', 'status' => '1','stripe_country' =>'1'],
			['code' => 'UM', 'name' => 'United States Minor Outlying Islands', 'iso3' => NULL, 'phone_code' => NULL, 'phone_code' => '1', 'status' => '1','stripe_country' =>'0'],
			['code' => 'UY', 'name' => 'Uruguay', 'iso3' => 'URY', 'phone_code' => '858', 'phone_code' => '598', 'status' => '1','stripe_country' =>'0'],
			['code' => 'UZ', 'name' => 'Uzbekistan', 'iso3' => 'UZB', 'phone_code' => '860', 'phone_code' => '998', 'status' => '1','stripe_country' =>'0'],
			['code' => 'VU', 'name' => 'Vanuatu', 'iso3' => 'VUT', 'phone_code' => '548', 'phone_code' => '678', 'status' => '1','stripe_country' =>'0'],
			['code' => 'VE', 'name' => 'Venezuela', 'iso3' => 'VEN', 'phone_code' => '862', 'phone_code' => '58', 'status' => '1','stripe_country' =>'0'],
			['code' => 'VN', 'name' => 'Viet Nam', 'iso3' => 'VNM', 'phone_code' => '704', 'phone_code' => '84', 'status' => '1','stripe_country' =>'0'],
			['code' => 'VG', 'name' => 'Virgin Islands, British', 'iso3' => 'VGB', 'phone_code' => '92', 'phone_code' => '1284', 'status' => '1','stripe_country' =>'0'],
			['code' => 'VI', 'name' => 'Virgin Islands, U.s.', 'iso3' => 'VIR', 'phone_code' => '850', 'phone_code' => '1340', 'status' => '1','stripe_country' =>'0'],
			['code' => 'WF', 'name' => 'Wallis and Futuna', 'iso3' => 'WLF', 'phone_code' => '876', 'phone_code' => '681', 'status' => '1','stripe_country' =>'0'],
			['code' => 'EH', 'name' => 'Western Sahara', 'iso3' => 'ESH', 'phone_code' => '732', 'phone_code' => '212', 'status' => '1','stripe_country' =>'0'],
			['code' => 'YE', 'name' => 'Yemen', 'iso3' => 'YEM', 'phone_code' => '887', 'phone_code' => '967', 'status' => '1','stripe_country' =>'0'],
			['code' => 'ZM', 'name' => 'Zambia', 'iso3' => 'ZMB', 'phone_code' => '894', 'phone_code' => '260', 'status' => '1','stripe_country' =>'0'],
			['code' => 'ZW', 'name' => 'Zimbabwe', 'iso3' => 'ZWE', 'phone_code' => '716', 'phone_code' => '263', 'status' => '1','stripe_country' =>'0'],

		]);
	}
}
