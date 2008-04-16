function p(o)
{
	var s = '';
	for (var i in o) {
		s += i+' => '+o[i]+"\n";
	}
	return s;

}
