/* Buduje samodzielny index.html z page.html (źródło współdzielone z Artifactem). */
const fs = require("fs");
const raw = fs.readFileSync("page.html", "utf8");

/* page.html jest jednocześnie źródłem Artifacta, więc zaczyna się od <title>
   i <link>-ów do fontów. W samodzielnym dokumencie ich miejsce jest w <head>,
   nie w <body> — wycinam je z góry pliku i przenoszę wyżej. Bez tego
   index.html miał dwa <title> naraz, a to niepoprawny HTML. */
const HEAD_TAG = /^\s*(?:<title>[^<]*<\/title>|<link\b[^>]*>)/i;
let body = raw, hoisted = [], m;
while ((m = body.match(HEAD_TAG))) {
  hoisted.push(m[0].trim());
  body = body.slice(m[0].length);
}
body = body.replace(/^\s*\n/, "");
const title = (raw.match(/<title>([^<]*)<\/title>/) || [, "Elven Silk"])[1];
const headLinks = hoisted.filter(function (tag) { return !/^<title/i.test(tag); }).join("\n");

/* FAQ do danych strukturalnych wyciągam z TEJ SAMEJ tablicy, którą renderuje strona.
   Wcześniej pytania były przepisane ręcznie i zdążyły się rozjechać z treścią: schema
   miała osiem pytań, strona dziewięć, a trzy pozycje pochodziły w ogóle z innej sekcji.
   Google wymaga, żeby FAQPage odpowiadał temu, co widać — rozjazd kosztuje wynik. */
function extractArray(src, decl) {
  const start = src.indexOf(decl);
  if (start < 0) throw new Error("nie znalazłem " + decl + " w page.html");
  let i = src.indexOf("[", start), depth = 0, quote = null;
  for (let j = i; j < src.length; j++) {
    const c = src[j], prev = src[j - 1];
    if (quote) { if (c === quote && prev !== "\\") quote = null; continue; }
    if (c === '"' || c === "'" || c === "`") { quote = c; continue; }
    if (c === "[") depth++;
    else if (c === "]") { depth--; if (!depth) return src.slice(i, j + 1); }
  }
  throw new Error("nie domknąłem tablicy " + decl);
}
const FAQ   = eval(extractArray(raw, "var FAQ = "));     /* własne dane, same literały */
const SIZES = eval(extractArray(raw, "var SIZES = "));
/* Cennik czytam z tej samej tabeli progów, z której liczy konfigurator. Gdyby kwoty
   były tu przepisane, dane strukturalne rozjechałyby się ze stroną przy pierwszej
   zmianie cen — a wynik w wyszukiwarce liczy się po tym, co widzi klient. */
const TIERS = eval(extractArray(raw, "var ROSE_TIERS = "));
function roseUnit(k) {
  for (const tr of TIERS) { if (k <= tr.to) return tr.unit; }
  return TIERS[TIERS.length - 1].unit;
}
const rosesPrice = (k) => k * roseUnit(k);
const PRICE_MIN = rosesPrice(1);
const PRICE_MAX = Math.max.apply(null, SIZES.map(function (z) { return rosesPrice(z.n); }));

/* Odpowiedź o cenie ma w HTML-u puste miejsca (<b data-price="from">—</b>), które
   wypełnia dopiero JS na stronie. Bez tego podstawienia do danych strukturalnych
   trafiał goły myślnik zamiast kwoty. */
const zl = (v) => v + " zł";
function stripTags(h) {
  return String(h)
    .replace(/<b[^>]*data-price="from"[^>]*>.*?<\/b>/gi, zl(PRICE_MIN))
    .replace(/<b[^>]*data-price="to"[^>]*>.*?<\/b>/gi,   zl(PRICE_MAX))
    .replace(/<[^>]+>/g, " ")
    .replace(/\s+([,.;:])/g, "$1")
    .replace(/\s+/g, " ")
    .trim();
}

const DESC = "Ręcznie tworzone bukiety z satynowych róż — wieczne kwiaty na urodziny, rocznicę i osiemnastkę. "
  + "Złóż swój bukiet w konfiguratorze: dowolna liczba róż od jednej w górę, kolor, oprawa z organzy, korona, LED. "
  + "Odbiór w Kaliszu, wysyłka InPost i Furgonetka.";
const URL = "https://www.elvensilk.com/";

const ld = {
  "@context": "https://schema.org",
  "@graph": [
    {
      "@type": ["LocalBusiness", "Store"],
      "@id": URL + "#biz",
      name: "Elven Silk",
      description: "Atelier ręcznie tworzonych bukietów z wstążki satynowej, kartek okolicznościowych i boxów prezentowych.",
      image: URL + "assets/swiecacy-led-1000.webp",
      email: "elvensilk@wp.pl",
      url: URL,
      priceRange: PRICE_MIN + "–" + PRICE_MAX + " zł",
      telephone: "+48781893346",
      address: {
        "@type": "PostalAddress",
        streetAddress: "ul. Częstochowska 140",
        postalCode: "62-800",
        addressLocality: "Kalisz",
        addressRegion: "wielkopolskie",
        addressCountry: "PL"
      },
      sameAs: ["https://www.instagram.com/elvensilkofficial/"],
      makesOffer: {
        "@type": "Offer",
        itemOffered: { "@type": "Product", name: "Bukiet z satynowych róż na zamówienie" },
        priceCurrency: "PLN",
        priceSpecification: { "@type": "PriceSpecification", minPrice: PRICE_MIN, priceCurrency: "PLN" }
      }
    },
    {
      "@type": "WebSite",
      "@id": URL + "#site",
      url: URL,
      name: "Elven Silk Atelier",
      inLanguage: "pl-PL",
      publisher: { "@id": URL + "#biz" }
    },
    {
      /* Jak powstaje róża — najczęściej cytowany fragment przez wyszukiwarki AI,
         bo odpowiada na pytanie „jak się to robi" konkretem, a nie ogólnikiem. */
      "@type": "HowTo",
      "@id": URL + "#howto",
      name: "Jak powstaje róża z satynowej wstążki",
      description: "Technika szycia róży z wstążki satynowej: wycinanie płatków, opalanie krawędzi nad płomieniem i zwijanie na łodydze.",
      totalTime: "PT4H",
      supply: [
        { "@type": "HowToSupply", name: "wstążka satynowa" },
        { "@type": "HowToSupply", name: "łodyga florystyczna" }
      ],
      tool: [{ "@type": "HowToTool", name: "źródło otwartego płomienia do opalania krawędzi" }],
      step: [
        { "@type": "HowToStep", position: 1, name: "Wycięcie płatków",
          text: "Każdy płatek wycinam ze wstążki pojedynczo. Jedna róża to kilkanaście płatków." },
        { "@type": "HowToStep", position: 2, name: "Opalenie krawędzi",
          text: "Krawędź każdego płatka opalam nad płomieniem. Satyna jest tkaniną, więc przecięta osypuje się po kilku dniach — opalenie zatapia brzeg i zamyka włókna na stałe." },
        { "@type": "HowToStep", position: 3, name: "Zwinięcie róży",
          text: "Płatki zwijam kolejno wokół łodygi, od najmniejszych w środku po największe na zewnątrz." },
        { "@type": "HowToStep", position: 4, name: "Kompozycja bukietu",
          text: "Gotowe główki układam w kopułę, dobieram opakowanie, koronę, podświetlenie lub wstążkę z imieniem." },
        { "@type": "HowToStep", position: 5, name: "Pakowanie",
          text: "Bukiet jedzie w sztywnym kartonie z wypełnieniem, z osobno zabezpieczoną kopułą." }
      ]
    },
    {
      "@type": "Article",
      "@id": URL + "#poradnik",
      headline: "Jak powstaje satynowa róża i czym różni się od innych kwiatów, które nie więdną",
      description: "Technika szycia róży z satyny, porównanie z różami stabilizowanymi, mydlanymi i piankowymi, dobór liczby róż oraz pielęgnacja bukietu.",
      inLanguage: "pl-PL",
      datePublished: "2026-09-14",
      dateModified: new Date().toISOString().slice(0, 10),
      isPartOf: { "@id": URL + "#site" },
      publisher: { "@id": URL + "#biz" },
      author: { "@id": URL + "#biz" },
      about: [
        { "@type": "Thing", name: "róże z wstążki satynowej" },
        { "@type": "Thing", name: "kwiaty wieczne" },
        { "@type": "Thing", name: "rękodzieło" }
      ]
    },
    {
      "@type": "Product",
      "@id": URL + "#produkt",
      name: "Bukiet z satynowych róż",
      description: "Ręcznie tworzony bukiet z róż z wstążki satynowej. Dowolna liczba róż — od jednej w górę, z gotowymi zestawami 7, 19, 37 i 101 róż. "
        + "Trzynaście kolorów satyny, oprawa z organzy, opakowanie i dodatki do wyboru. Cena za różę spada z liczbą sztuk: od 15 zł przy jednej do 11 zł od szesnastu.",
      image: URL + "assets/cfg/preview-czerwony-org-biala-900.webp",
      brand: { "@id": URL + "#biz" },
      material: "wstążka satynowa",
      offers: {
        "@type": "AggregateOffer",
        priceCurrency: "PLN",
        lowPrice: PRICE_MIN,
        highPrice: PRICE_MAX,
        offerCount: SIZES.length,
        availability: "https://schema.org/MadeToOrder",
        seller: { "@id": URL + "#biz" }
      }
    },
    {
      "@type": "FAQPage",
      "@id": URL + "#faq",
      mainEntity: FAQ.map(function (f) {
        return {
          "@type": "Question",
          name: f.q.pl,
          acceptedAnswer: { "@type": "Answer", text: stripTags(f.a.pl) }
        };
      })
    }
  ]
};

const html = `<!doctype html>
<html lang="pl">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>${title}</title>
${headLinks}
<meta name="description" content="${DESC}">
<meta name="theme-color" content="#FBF7F4" media="(prefers-color-scheme: light)">
<meta name="theme-color" content="#120C11" media="(prefers-color-scheme: dark)">
<link rel="canonical" href="${URL}">
<link rel="icon" href="assets/logo-mark.png" type="image/png" sizes="256x256">
<link rel="apple-touch-icon" href="assets/logo-mark.png">
<link rel="preload" as="image" href="assets/hero-poster-1280.webp" fetchpriority="high">
<meta name="robots" content="index,follow,max-image-preview:large,max-snippet:-1">
<meta property="og:type" content="website">
<meta property="og:locale" content="pl_PL">
<meta property="og:locale:alternate" content="en_GB">
<meta property="og:locale:alternate" content="uk_UA">
<meta property="og:site_name" content="Elven Silk">
<meta property="og:title" content="Elven Silk — bukiety z satynowych róż, które nie więdną">
<meta property="og:description" content="${DESC}">
<meta property="og:url" content="${URL}">
<meta property="og:image" content="${URL}assets/swiecacy-led-1000.webp">
<meta name="twitter:card" content="summary_large_image">
<script type="application/ld+json">${JSON.stringify(ld)}</script>
<style>
  html{color-scheme:light dark}
  body{margin:0;font-family:system-ui,sans-serif}
  img{max-width:100%;height:auto}
  [hidden]{display:none !important}
</style>
</head>
<body>
${body}
</body>
</html>
`;
fs.writeFileSync("index.html", html);
console.log("index.html:", (html.length / 1024).toFixed(1), "kB");

/* ─────────────────────────────────────────────────────────────────────────────
   Pliki dla wyszukiwarek — generowane tu, żeby nie rozjechały się z treścią.
   ───────────────────────────────────────────────────────────────────────────── */
const HOST = URL.replace(/\/$/, "");

/* Roboty AI wpuszczam jawnie. Domyślne „Allow: /" wystarcza technicznie, ale część
   z nich czyta wyłącznie własną sekcję — bez niej bywają traktowane jak niewpuszczone. */
const AI_BOTS = [
  "GPTBot", "ChatGPT-User", "OAI-SearchBot",          // OpenAI
  "ClaudeBot", "Claude-User", "Claude-SearchBot",      // Anthropic
  "PerplexityBot", "Perplexity-User",                  // Perplexity
  "Google-Extended",                                   // Gemini / AI Overviews
  "Applebot-Extended",                                 // Apple Intelligence
  "meta-externalagent",                                // Meta AI
  "Amazonbot", "DuckAssistBot", "cohere-ai", "YouBot", "CCBot"
];
const robots = [
  "# Elven Silk — atelier bukietów z satynowych róż, Kalisz",
  "",
  "User-agent: *",
  "Allow: /",
  "",
  "# Wyszukiwarki generatywne — wpuszczone celowo.",
  "# Jeśli właścicielka zmieni zdanie, zamień Allow na Disallow w tej sekcji.",
  ...AI_BOTS.map(function (b) { return "User-agent: " + b + "\nAllow: /\n"; }),
  "Sitemap: " + HOST + "/sitemap.xml"
].join("\n") + "\n";
fs.writeFileSync("robots.txt", robots);

/* llms.txt — mapa strony dla modeli językowych. Strona jest jednostronicowa, więc
   poza odnośnikami do kotwic wkładam też same fakty: to one trafiają do cytowań. */
const llms = `# Elven Silk

> Atelier ręcznie szytych bukietów z róż z wstążki satynowej. Kalisz, Polska.
> Bukiety nie więdną, bo nie zawierają żywej rośliny — każda róża jest zwijana ręcznie
> z osobno wyciętych i opalanych płatków satyny.

Jednostronicowa witryna z konfiguratorem bukietu. Treść w trzech językach: polskim,
angielskim i ukraińskim. Wszystkie ceny brutto w złotych.

## Produkt

- [Konfigurator bukietu](${HOST}/#konfigurator): liczba róż dowolna, od jednej w górę —
  gotowe zestawy to 7 róż (ok. 25 cm), 19 róż (ok. 35 cm), 37 róż (ok. 45 cm) i 101 róż
  (ok. 65 cm). Do tego 13 kolorów satyny, oprawa z organzy (biała, czarna, różowa,
  jasnoniebieska albo bez organzy), opakowanie zewnętrzne i dodatki.
  Cena przeliczana na bieżąco.
- Cennik róż za sztukę: 1–3 róże po 15 zł, 4–7 po 14 zł, 8–11 po 13 zł, 12–15 po 12 zł,
  od 16 w górę po 11 zł. Stąd gotowe zestawy: 7 róż od 98 zł, 19 róż od 209 zł,
  37 róż od 407 zł, 101 róż od 1111 zł. Dodatki i dostawa doliczane osobno.
- [Kolekcja](${HOST}/#kolekcja): cztery gotowe zestawienia jako punkt wyjścia.
- [Realizacje](${HOST}/#realizacje): 18 zdjęć zrealizowanych zamówień.

## Poradnik

- [Jak powstaje róża z satynowej wstążki](${HOST}/#poradnik): płatki wycinane pojedynczo,
  krawędź opalana nad płomieniem (satyna jako tkanina inaczej się osypuje), zwijanie na
  łodydze od najmniejszych płatków w środku. Jedna róża to kilkanaście płatków, bukiet
  z 19 róż to kilka godzin pracy.
- [Satyna a inne kwiaty wieczne](${HOST}/#poradnik): róże stabilizowane to prawdziwe kwiaty
  konserwowane gliceryną — najdroższe, źle znoszą wilgoć. Mydlane kruszą się i nie znoszą
  wody. Piankowe (foamiran) są najtańsze, ale wyraźnie sztuczne. Satynowe są z tkaniny:
  nie więdną, można z nich zetrzeć kurz, blakną w bezpośrednim słońcu.
- [Dobór liczby róż](${HOST}/#poradnik): 1–5 róż to pojedyncza róża albo mała wiązanka,
  7 róż ≈ 25 cm, 19 róż ≈ 35 cm to rozmiar najczęściej wybierany, 37 róż ≈ 45 cm na
  osiemnastkę i duże rocznice, 101 róż ≈ 65 cm na oświadczyny i jubileusze.
- [Pielęgnacja](${HOST}/#poradnik): z dala od wilgoci, kaloryfera i słońca; kurz miękkim
  pędzelkiem lub zimnym nawiewem; nie prać i nie prasować.

## Dostawa i zakup

- [Dostawa i płatność](${HOST}/#dostawa): Paczkomat InPost gabaryt B 16,94 zł, gabaryt C
  19,44 zł, kurier InPost 22,13 zł, kurier z pobraniem 24,45 zł. Odbiór osobisty w Kaliszu
  bezpłatny. Zamówienia od 300 zł — dostawa gratis. Czas realizacji 2–5 dni roboczych,
  ekspres 48 h za dopłatą. Wysyłka na cały świat.
- [Współpraca B2B](${HOST}/#wspolpraca): produkcja pod cudzą marką, ceny hurtowe od 10 sztuk.
- [Pytania i odpowiedzi](${HOST}/#faq)

## Uwagi

- Bukiet składany w konfiguratorze jest towarem wykonanym na indywidualne zamówienie,
  więc nie obejmuje go prawo odstąpienia w 14 dni (art. 38 ust. 1 pkt 3 ustawy
  o prawach konsumenta). Odpowiedzialność za zgodność towaru z umową obowiązuje przez 2 lata.
- Produkt nie jest zabawką: zawiera drobne elementy, nieodpowiedni dla dzieci poniżej 3 lat.
  Wersja podświetlana zawiera baterie guzikowe.
- Zdjęcia w galerii realizacji przedstawiają prawdziwe zamówienia. Film w nagłówku, podgląd
  w konfiguratorze i zdjęcie pudełka to wizualizacje poglądowe wygenerowane z pomocą AI
  i są tak oznaczone. Gotowy bukiet może się od nich różnić; wiąże potwierdzona specyfikacja.

## Kontakt

- Adres i telefon to dane punktu Furgonetka: ul. Częstochowska 140, 62-800 Kalisz, Polska,
  tel. +48 781 893 346. Tam odbiera się zamówienia i tam trafia korespondencja.
- Sprzedaż prowadzona w ramach działalności nierejestrowanej (art. 5 ust. 1 Prawa
  przedsiębiorców) — bez wpisu w CEIDG, a więc bez numeru NIP i REGON. Prawa konsumenta
  obowiązują bez zmian.
- E-mail: elvensilk@wp.pl
- Instagram: https://www.instagram.com/elvensilkofficial/
`;
fs.writeFileSync("llms.txt", llms);

const today = new Date().toISOString().slice(0, 10);
fs.writeFileSync("sitemap.xml",
  `<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
  <url>
    <loc>${URL}</loc>
    <lastmod>${today}</lastmod>
    <changefreq>monthly</changefreq>
    <priority>1.0</priority>
  </url>
</urlset>
`);

console.log("robots.txt, llms.txt, sitemap.xml: zapisane");
