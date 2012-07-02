var lang = {
    'loading':'Loading...',
    'cat_audio':'Audio',
    'cat_music':'Audio',
    'cat_video':'Video',
    'cat_images':'Pictures',
    'cat_search':'Search',
    'search_lineLabel' : 'Keyword:',
    'search_plaseholder' : 'Input search text here',
    'list_page':'Page ',
    'list_from':'of',
    'list_fined':'Found',
    'list_records':'records',
	'list_records':'records of',
    'sec':'sec',
    'min':'min',
    'hrs':'h',
	"Jan":"January",
	"Feb":"February",
	"Ma":"March",
	"Apr":"April",
	"May":"May",
	"Jn":"June",
	"Jl":"July",
	"Ag":"August",
	"Sep":"September",
	"Oct":"October",
	'Now':'November',
	"Dec": "December",
	"add":"Posted:",
	"No play":"No playable content here",
	"NotF":'Nothing was found',
	"alert":"Alert",
	"mes":"Message",
	"subt":"Subtitles",
	"audio":"Audio track",
        "3dmode":"3D mode",
        "mode":"Mode",
	"off":"Off",
	"unk":"Unknown",
	"nota":"None"
};
var iso639 = [
  {
    code : ['eng','en'],
    name : 'English'
  },
  {
    code : ['rus','ru'],
    name : 'Russian'
  },
  {
    code : ['ukr','uk'],
    name : 'Ukrainian'
  },
  {
    code : ['bel','be'],
    name : 'Belarusian'
  },
  {
    code : ['fre','fra','fr'],
    name : 'French'
  },
  {
    code : ['ger','deu','de'],
    name : 'German'
  },
  {
    code : ['ita','it'],
    name : 'Italian'
  },
  {
    code : ['spa','es'],
    name : 'Spanish'
  },
  {
    code : ['por','pt'],
    name : 'Portuguese'
  },
  {
    code : ['swe','sv'],
    name : 'Swedish'
  },
  {
    code : ['nor','no'],
    name : 'Norwegian'
  },
  {
    code : ['dut','nld','nl'],
    name : 'Dutch'
  },
  {
    code : ['srp','scc','sr'],
    name : 'Serbian'
  },
  {
    code : ['slv','sl'],
    name : 'Slovenian'
  },
  {
    code : ['hrv','hr','scr'],
    name : 'Croatian'
  },
  {
    code : ['alb','sqi','sq'],
    name : 'Albanian'
  },
  {
    code : ['jpn','ja'],
    name : 'Japanese'
  },
  {
    code : ['chi','zho','zh'],
    name : 'Chinese'
  },
  {
    code : ['kor','ko'],
    name : 'Korean'
  },
  {
    code : ['vie','vi'],
    name : 'Vietnamese'
  },
  {
    code : ['lav','lv'],
    name : 'Latvian'
  },
  {
    code : ['lit','lt'],
    name : 'Lithuanian'
  },
  {
    code : ['est','et'],
    name : 'Estonian'
  },
  {
    code : ['fin','fi'],
    name : 'Finnish'
  },
  {
    code : ['hun','hu'],
    name : 'Hungarian'
  },
  {
    code : ['cze','ces','cs'],
    name : 'Czech'
  },
  {
    code : ['slo','slk','sk'],
    name : 'Slovak'
  },
  {
    code : ['bul','bg'],
    name : 'Bulgarian'
  },
  {
    code : ['pol','pl'],
    name : 'Polish'
  },
  {
    code : ['rum','ron','ro'],
    name : 'Romanian'
  },
  {
    code : ['gre','ell','el'],
    name : 'Greek'
  },
  {
    code : ['heb','he'],
    name : 'Hebrew'
  },
  {
    code : ['tur','tr'],
    name : 'Turkish'
  },
  {
    code : ['dan','da'],
    name : 'Danish'
  },
  {
    code : ['ice','isl','is'],
    name : 'Icelandic'
  },
  {
    code : ['hin','hi'],
    name : 'Hindi'
  },
  {
    code : ['ben','bn'],
    name : 'Bengali'
  },
  {
    code : ['ara','ar'],
    name : 'Arabic'
  },
  {
    code : ['arm','hye','hy'],
    name : 'Armenian'
  },
  {
    code : ['geo','kat','ka'],
    name : 'Georgian'
  },
  {
    code : ['aze','az'],
    name : 'Azerbaijani'
  },
  {
    code : ['bak','ba'],
    name : 'Bashkir'
  },
  {
    code : ['baq','eus','eu'],
    name : 'Basque'
  },
  {
    code : ['bos','bs'],
    name : 'Bosnian'
  },
  {
    code : ['bua'],
    name : 'Buriat'
  },
  {
    code : ['bur','mya','my'],
    name : 'Burmese'
  },
  {
    code : ['che','ce'],
    name : 'Chechen'
  },
  {
    code : ['wel','cym','cy'],
    name : 'Welsh'
  },
  {
    code : ['dzo','dz'],
    name : 'Dzongkha'
  },
  {
    code : ['epo','eo'],
    name : 'Esperanto'
  },
  {
    code : ['per','fa'],
    name : 'Persian'
  },
  {
    code : ['gle','ga'],
    name : 'Irish'
  },
  {
    code : ['guj','gu'],
    name : 'Gujarati'
  },
  {
    code : ['ind','id'],
    name : 'Indonesian'
  },
  {
    code : ['ira'],
    name : 'Iranian'
  },
  {
    code : ['kas','ks'],
    name : 'Kashmiri'
  },
  {
    code : ['kaz','kk'],
    name : 'Kazakh'
  },
  {
    code : ['kbd'],
    name : 'Kabardian'
  },
  {
    code : ['kom','kv'],
    name : 'Komi'
  },
  {
    code : ['krl'],
    name : 'Karelian'
  },
  {
    code : ['kur','ku'],
    name : 'Kurdish'
  },
  {
    code : ['mar','mr'],
    name : 'Marathi'
  },
  {
    code : ['mac','mkd','mk'],
    name : 'Macedonian'
  },
  {
    code : ['nep','ne'],
    name : 'Nepali'
  },
  {
    code : ['oss','os'],
    name : 'Ossetian'
  },
  {
    code : ['sah'],
    name : 'Yakut'
  },
  {
    code : ['som','so'],
    name : 'Somali'
  },
  {
    code : ['tam','ta'],
    name : 'Tamil'
  },
  {
    code : ['tat','tt'],
    name : 'Tatar'
  },
  {
    code : ['tel','te'],
    name : 'Telugu'
  },
  {
    code : ['tgk','tg'],
    name : 'Tajik'
  },
  {
    code : ['tha','th'],
    name : 'Thai'
  },
  {
    code : ['tuk','tk'],
    name : 'Turkmen'
  },
  {
    code : ['udm'],
    name : 'Udmurt'
  },
  {
    code : ['urd','ur'],
    name : 'Urdu'
  },
  {
    code : ['uzb','uz'],
    name : 'Uzbek'
  },
  {
    code : ['xal'],
    name : 'Kalmyk'
  },
  {
    code : ['tib','bod','bo'],
    name : 'Tibetan'
  },
  {
    code : ['yid','yi'],
    name : 'Yiddish'
  }
];