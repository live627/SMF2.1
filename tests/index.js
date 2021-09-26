import test from 'ava';
import '../Themes/default/scripts/script.js';

test('String.prototype.php_to8bit', t =>
{
	var charMap = {
		A: [65],
		'ñ': [195, 177],
		'Ö': [195, 150],
		'ṍ': [225, 185, 141],
		'😭': [240, 159, 152, 173]
	};
	for (const [char, codes] of Object.entries(charMap))
	{
		t.deepEqual([...char.php_to8bit()].map(x => x.charCodeAt(0)), codes);
		t.deepEqual(Buffer.from(char, 'utf-8').toJSON().data, codes);
	}
});
