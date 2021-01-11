<?php
namespace shotoftruth {
	class ShotOfTruth {
		const EPISODES_PER_PAGE = 10;
		protected $site_sxe;
		protected $pages = [];
		function __construct(string $rss_xml) {
			if(empty($rss_xml)) {
				throw new \InvalidArgumentException('rss_xml cannot be empty');
			}
			$this->site_sxe = new \SimpleXMLElement($rss_xml,LIBXML_NOCDATA);
			$this->site_sxe->registerXPathNamespace('itunes','http://www.itunes.com/dtds/podcast-1.0.dtd');
			$this->site_sxe->registerXPathNamespace('content','http://purl.org/rss/1.0/modules/content/');
		}
		/**
		 * Rebuilds the website pages
		 * @return NULL
		 */
		public function regenSite() {
			$this->pages = [];
			self::writeIndexPage();
			self::writeAboutPage();
			self::writeHirePage();
			self::writeAllEpisodePages();
			self::writeEpisodeListPage();
			self::writeSiteMap();
		}


		/**
		 * Writes XML for site map
		 * @return string
		 */
		protected function writeSiteMap() {
			$date_str = date("Y-m-d");
			$writer = new \XMLWriter;
			$writer->openURI('sitemap.xml');
			$writer->startDocument('1.0', 'UTF-8');
			$writer->setIndent(1);
			$writer->startElement('urlset');
			$writer->writeAttribute(
				'xmlns',
				'http://www.sitemaps.org/schemas/sitemap/0.9'
			);
			foreach($this->pages as $page) {
				$writer->startElement('url');
				$writer->writeElement('loc',"https://shotoftruthpodcast.com/$page");
				$writer->writeElement('lastmod',$date_str);
				$writer->writeElement('changefreq','weekly');
				$writer->endElement();
			}
			$writer->endElement();
			ob_start();
			$writer->flush();
		}

		protected function getOpenGraphTags(string $title, string $url, \SimpleXMLElement $episode = null) {
			$og_title = $title;
			$og_image = 'https://shotoftruthpodcast.com/images/home-logo.png';
			$og_type = 'website';
			$og_url = $url;
			ob_start();
			if (!is_null($episode)) {
				$og_type = 'audio/mpeg';
				$og_audio_url = (string)$episode->enclosure->attributes()->url[0];
				$og_description = $episode->xpath('itunes:subtitle')[0];
				?>
				<meta property="og:title" content="<?=$og_title?>" />
				<meta property="og:type" content="<?=$og_type?>" />
				<meta property="og:url" content="<?=$og_url?>" />
				<meta property="og:image" content="<?=$og_image?>" />
				<meta property="og:audio" content="<?=$og_audio_url?>" />
				<meta property="og:audio:secure_url" content="<?=$og_audio_url?>" />
				<meta property="og:description"  content="<?=$og_description?>" />
				<?php
			} else {
				?>
				<meta property="og:title" content="<?=$og_title?>" />
				<meta property="og:type" content="<?=$og_type?>" />
				<meta property="og:url" content="<?=$og_url?>" />
				<meta property="og:image" content="<?=$og_image?>" />
				<?php
			}
			return trim(ob_get_clean());
		}

		/**
		 * @param  string $title titile of the page
		 * @return string The beginning of an HTML page
		 */
		protected function getHeaderHtml(string $title, $url, \SimpleXMLElement $episode = null) {
			ob_start();
			?>
			<!doctype html>
			<html lang="en">
			<head>
				<!-- Required meta tags -->
				<meta charset="utf-8">
				<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
				<!-- Bootstrap CSS -->
				<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css" integrity="sha384-MCw98/SFnGE8fJT3GXwEOngsV7Zt27NXFoaoApmYm81iuXoPkFOJwJ8ERdknLPMO" crossorigin="anonymous">
				<link rel="stylesheet" href="/styles.css">
				<link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">
				<link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
				<link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">
				<link rel="manifest" href="/site.webmanifest">
				<title><?=$title?></title>
				<?=self::getOpenGraphTags($title, $url, $episode)?>
				<script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
				new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
				j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
				'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
				})(window,document,'script','dataLayer','GTM-KKHFPHZ');</script>
				<!-- End Google Tag Manager -->
				<!-- Global site tag (gtag.js) - Google Analytics -->
				<script async src="https://www.googletagmanager.com/gtag/js?id=UA-150299611-1"></script>
				<!-- Google Tag Manager -->
				<script>
					window.dataLayer = window.dataLayer || [];
					function gtag(){dataLayer.push(arguments);}
					gtag('js', new Date());
					gtag('config', 'UA-150299611-1');
				</script>
			</head>
			<body class="shot-of-truth">
				<nav class="navbar fixed-top navbar-expand-lg navbar-light bg-light">
					<a class="navbar-brand" href="/">
						<img src="images/home-logo.png" width="30" height="30" class="d-inline-block align-top" alt="">
						A Shot Of Truth Podcast
					</a>
					<button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
						<span class="navbar-toggler-icon"></span>
					</button>
					<div class="collapse navbar-collapse" id="navbarSupportedContent">
						<ul class="navbar-nav mr-auto">
							<li class="nav-item">
								<a class="nav-link" href="/">Home</a>
							</li>
							<li class="nav-item">
								<a class="nav-link" href="/<?=self::getPodcastsFilename()?>">Podcast Episodes</a>
							</li>
							<li class="nav-item">
								<a class="nav-link" href="/about">About</a>
							</li>
							<li class="nav-item">
								<a class="nav-link" href="/hire">Hire</a>
							</li>
						<!--li class="nav-item">
							<a class="nav-link" href="about-us.html">About Us</a>
						</li-->
						<!-- <li class="nav-item dropdown">
							<a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
								Podcast Episodes
							</a>
							<div class="dropdown-menu" aria-labelledby="navbarDropdown">
								<?php
								$count = 0;
								foreach($this->site_sxe->channel->item as $episode) {
									?><a class="dropdown-item" href="/<?=self::getEpisodeFilename($count++)?>"><?=$episode->title?></a><?php
								}
								?>
							</div>
						</li> -->
					</ul>
					<!-- <form class="form-inline my-2 my-lg-0">
						<input class="form-control mr-sm-2" type="search" placeholder="Search" aria-label="Search">
						<button class="btn btn-outline-success my-2 my-sm-0" type="submit">Search</button>
					</form> -->
				</div>
			</nav>
			<?php
			return (trim(ob_get_clean()));
		}
		/**
		 * Returns the footer html for the website
		 * @return string Footer HTML
		 */
		protected function getFooterHtml() {
			ob_start();
			?>
			<!-- Optional JavaScript -->
			<!-- jQuery first, then Popper.js, then Bootstrap JS -->
			<script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
			<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.3/umd/popper.min.js" integrity="sha384-ZMP7rVo3mIykV+2+9J3UJ46jBk0WLaUAdn689aCwoqbBJiSnjAK/l8WvCWPIPm49" crossorigin="anonymous"></script>
			<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/js/bootstrap.min.js" integrity="sha384-ChfqqxuZUCnJSK3+MXmPNIyE6ZbWh2IMqE241rYiqJxyMiZ6OW/JmZQ5stwEULTy" crossorigin="anonymous"></script>
			<script type="text/javascript" src="script.js"></script>
			<!-- Google Tag Manager (noscript) -->
			<noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-KKHFPHZ"
			height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
			<!-- End Google Tag Manager (noscript) -->
		</body>
		</html>
		<?php
		return trim(ob_get_clean());
	}
		/**
		 * Gets the episode filename
		 * @param int  $episode_number
		 * @param bool $include_extension Optional Whether or not to include the file extension
		 * @return string                 Name of file based on episode number
		 */
		public static function getEpisodeFilename(int $episode_number, bool $include_extension = false) {
			if ( $episode_number < 0 ) {
				$filename = 'trailer';
			} else {
				$filename = "$episode_number";
			}
			$filename .= $include_extension ? '.html' : '';
			return $filename;
		}
		/**
		 * Gets the name of the podcast episodes page
		 * @param bool $include_extension Optional Whether or not to include the file extension
		 * @return string                 Name of podcast episodes page
		 */
		public static function getPodcastsFilename(bool $include_extension = false) {
			$filename = 'podcasts';
			$filename .= $include_extension ? '.html' : '';
			return $filename;

			return 'podcasts'. $include_extension ? '.html' : '';
		}
		/**
		 * Given an episode returns the description, optionally
		 * strips of Find Us: section
		 * @param \SimpleXMLElement $episode Episode XML Element
		 * @param bool|bool $include_contact_info Whether to include Find Us section
		 * @return string
		 */
		public static function getEpisodeDescription(\SimpleXMLElement $episode, bool $include_contact_info = false) {
			$description = $episode->xpath('content:encoded')[0];
			if(
				!$include_contact_info
				&& ($idx = strpos($description, 'Find Us:')) !== false ) {
				$description = rtrim(trim(substr($episode->xpath('content:encoded')[0], 0, $idx),'<br>'));
			}
			$description = str_replace('Show Notes:', '<span class="show-notes">Show Notes:</span>', $description);
			return trim($description);
		}
		/**
		 * Gets the <main> content for an episode page
		 * @param \SimpleXMLElement $episode Single Episode
		 * @return string
		 */
		protected function getEpisodePageHtml(\SimpleXMLElement $episode, int $episode_number) {
			$html = self::getHeaderHtml($episode->title.' - A Shot of Truth Podcast', 'https://shotoftruthpodcast.com/'.self::getEpisodeFilename($episode_number), $episode);
			ob_start();
			?>
			<main class="episode-page">
				<section>
					<h1 class="episode-title"><?=$episode->title?></h1>
					<h2 class="episode-subtitle"><?=$episode->xpath('itunes:subtitle')[0]?></h2>
					<h4 class="episode-date"><?=date('F j, Y',strtotime($episode->pubDate))?></h2>
						<h4 class="episode-duration"><?=$episode->xpath('itunes:duration')[0]?></h2>
							<article class="description">
								<p><?=self::getEpisodeDescription($episode)?></p>
							</article>
						</section>
						<section class="player-container">
							<figure>
								<figcaption>Listen Here:</figcaption>
								<audio
								controls
								src="<?=$episode->enclosure->attributes()->url?>"
								title="Listen to the episode, <?=$episode->title?> here"
								>
								Your browser does not support playing audio
							</audio>
						</figure>
					</section>
				</main>
				<?php
				$html .= trim(ob_get_clean()) . self::getFooterHtml();
				return $html;
			}
		/**
		 * Writes each episode page html to disk
		 * @return string[] Array of filenames written
		 */
		protected function writeAllEpisodePages() {
			$items = $this->site_sxe->xpath('//item');
			$count = count($items) -1;
			$filenames = [];
			foreach( array_reverse($items) as $item) {
				$html = self::getEpisodePageHtml($item, --$count);
				$filename = self::getEpisodeFilename($count,true);
				file_put_contents($filename,$html);
				$this->pages[] = $filename;
				$filenames[] = $filename;
			}
			return $filenames;
		}
		/**
		 * Get HTML for index page
		 * @return string
		 */
		protected function getIndexHtml() {
			$html = self::getHeaderHtml('A Shot Of Truth Podcast','https://shotoftruthpodcast.com');
			ob_start();
			?>
			<main class="home">
				<div class="jumbotron">
					<div class="container">
						<div class="row justify-content-center">
							<div class="col-lg-6">
								<h1 class="display-3">A Shot Of Truth</h1>
								<h2>With Victoria Matey</h2>
								<p>A podcast dedicated to building a network focused on knowledge sharing, celebrating, and storytelling for and with Undocumented people. There are millions of undocumented people in this country from all over the world. People who are impacted by Border Imperialism through family separation, displacement, detention, and more. We are here to document our history and in doing so connecting, building, healing in our ways.</p>
								<p>Our podcast has recorded with people throughout the country and in the last two years reached over 10 countries. As we evolve into this work we recognize it is a collective project--thank you for being a part of this!</p>
							</div>
						</div>
					</div>
				</div>
				<div class="container">
					<div class="row">
						<div class="col-lg-4">
							<div class="card m-1" >
								<div class="card-body">
									<h5 class="card-title">Where to Listen</h5>
									<h6 class="card-subtitle mb-2 text-muted">Subscribe to the Podcast</h6>
									<p class="card-text">You can find our podcast at the following places. More to come soon!</p>
									<a href="https://itunes.apple.com/us/podcast/a-shot-of-truth/id1436122328" class="card-link">Apple Podcasts</a>
									<a href="https://open.spotify.com/show/014rxomsYCAhWX98U1OMzk?si=jc7U--UbRIS0MZlCiL5vYA" class="card-link">Spotify</a>
									<a href="https://playmusic.app.goo.gl/?ibi=com.google.PlayMusic&isi=691797987&ius=googleplaymusic&apn=com.google.android.music&link=https://play.google.com/music/m/I3zqct2jo7tthib57vpvuxptr6y?t%3DA_Shot_Of_Truth%26pcampaignid%3DMKT-na-all-co-pr-mu-pod-16" class="card-link">Google Play</a>
									<a href="https://www.stitcher.com/s?fid=235842&refid=stpr" class="card-link">Stitcher</a>
									<a href="https://soundcloud.com/sudo-science/sets/a-shot-of-truth-podcast" class="card-link">SoundCloud</a>
									<a href="/shotoftruthpodcastrss.xml" class="card-link">RSS Feed</a>
								</div>
							</div>
						</div>
						<div class="col-lg-4">
							<div class="card m-1" >
								<div class="card-body">
									<h5 class="card-title">Join The Cause</h5>
									<h6 class="card-subtitle mb-2 text-muted">Help Us Reach More People</h6>
									<p class="card-text">You can become a contributing member. All funds goes directly to us, the podcasters and it's creation.</p>
									<a href="https://www.patreon.com/shotoftruthpodcast" class="card-link">Donate on Patreon</a>
								</div>
							</div>
						</div>
						<div class="col-lg-4">
							<div class="card m-1" >
								<div class="card-body">
									<h5 class="card-title">Reach Out</h5>
									<h6 class="card-subtitle mb-2 text-muted">Join the Community</h6>
									<p class="card-text">Follow, retweet, say hi. Give us feedback, ask us questions or tell us your story. We'd love to get to know you.</p>
									<a href="https://www.instagram.com/shotoftruthpodcast/" class="card-link">Instagram</a>
									<a href="https://www.facebook.com/ShotOfTruthPodcast" class="card-link">Facebook</a>
									<a href="https://twitter.com/shotoftruthpod" class="card-link">Twitter</a>
									<a href="https://www.tiktok.com/@shotoftruthpodcast" class="card-link">TikTok</a>
									<a href="mailto:info@shotoftruthpodcast.com" class="card-link">Email</a>
								</div>
							</div>
						</div>
					</div>
				</div>
			</main>
			<?php
			return $html.(trim(ob_get_clean())).self::getFooterHtml();
		}
		/**
		 * Get HTML for about page
		 * @return string
		 */
		protected function getAboutHtml() {
			$html = self::getHeaderHtml('About | A Shot Of Truth Podcast', 'https://shotoftruthpodcast.com/about');
			ob_start();
			?>
			<main class="about">
				<section class="section-wide">
					<div class="media-split">
						<img src="images/vicky_about.jpg" alt="Vicky on white background" class="media-split__img" />
						<article class="media-split__info">
							<header role="banner">
								<h1>About</h1>
								<h4>Victoria Matey (she/her/ella)</h4>
								<h5><span class="nowrap">Podcast Host,</span> <span class="nowrap">TEDx Speaker,</span> <span class="nowrap">Social Entrepreneur</span></h5>
							</header>
							<p>I’m a 28-year-old creator that's hoping to be a 70-year-old creator someday. I'm a creator of ideas, thoughts, content. I daydreamed my early 20s through Business school trying to understand my role in the world as a first-generation queer undocumented woman. Currently, I'm the host of a Shot of Truth Podcast--a podcast created for undocumented people and those affected by border imperialism.</p>
							<p>I love working with people and learning in community. During my free time, I like cooking, dancing, and lifting weights. I’ve released two TEDx talks and have been speaking all over the country for several years. I want to have fun and invest in my well being. I just want to be free. I want all of us to be. I hope my work speaks for itself--I love doing it.</p>
						</article>
					</div>
				</section>
				<section class="section-wide">
					<h2>TedX Talks</h2>
					<article class="two-up">
						<div class="two-up__item embed-responsive embed-responsive-16by9 shadow-sm rounded">
							<iframe src="https://www.youtube-nocookie.com/embed/Sam5bfKCtLI" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
						</div>
						<div class="two-up__item embed-responsive embed-responsive-16by9 shadow-sm rounded">
							<iframe src="https://www.youtube-nocookie.com/embed/mHr2MoAhSE0" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
						</div>
					</article>
				</section>
			</main>
			<?php
			return $html.(trim(ob_get_clean())).self::getFooterHtml();
		}
		/**
		 * Get HTML for about page
		 * @return string
		 */
		protected function getHireHtml() {
			$html = self::getHeaderHtml('Hire | A Shot Of Truth Podcast', 'https://shotoftruthpodcast.com/hire');
			ob_start();
			?>
			<main class="hire">
				<header class="u-text-center" role="banner">
					<h1>Hire</h1>
					<p>Currently, we are doing virtual workshops, conferences, and facilitations. I have been speaking since 2013 in places including The Wonder Womxn in Tech Conference, Instagram/Facebook Offices, Disney World, and more. Below you can find the topics I cover. I am passionate about doing this work and collective learning.</p>
					<p><a href="mailto:vicky@shotoftruthpodcast.com">Contact me for rates.</a></p>
				</header>
				<section class="section-wide">
					<article class="media-split">
						<img src="images/campus.jpg" alt="college campus" class="media-split__img shadow rounded">
						<div class="media-split__info">
							<header role="banner">
								<h2>Undocumented &amp; Higher Education</h2>
								<h6>2 Hours</h6>
								<h4>For Educators &amp; Student Affairs Professionals</h4>
							</header>
							<p>This workshop is catered to educators and student affairs professionals. It covers:</p>
							<ul>
								<li>The importance of being aware and conscious of the undocumented experience in higher education</li>
								<li>A brief explanation of HB1079 and DACA</li>
								<li>Heavily focuses on our own biases, internalized racism, and how to better support undocumented students</li>
							</ul>
							<p>This is not a how-to step by step but rather turning inwards with your team and accessing your power in making a difference in your institution for and with undocumented students.</p>
						</div>
					</article>
					<article class="media-split media-split--reverse">
						<img src="images/immigrant-dehumanization-exploitation-disposal.png" alt="The Criminalization, Exploitation, and Disposal of Immigrants presentation screen shot" class="media-split__img shadow rounded">
						<div class="media-split__info">
							<header role="banner">
								<h2>The Criminalization, Exploitation, and Disposal of Immigrants</h2>
								<h6>1 Hour</h6>
							</header>
							<p>The Criminalization, Exploitation, and Disposal of Immigrants in terms of history, policy, and the media. This is an extended version of my first <a href="https://www.youtube.com/watch?v=mHr2MoAhSE0" target="_blank">TEDx Talk</a>. We have to look at language, policies, institutions to understand the structural system racism forced upon immigrants and undocumented immigrants.</p>
							<p>Furthering my research on exploitation and detention this workshop will advance your understanding of the ideologies and institutions that criminalize, exploit, and dispose of immigrants. This workshop will help you view those ideologies and institutions through a different lens to better critically understand the context of US history and undocumented immigrants.</p>
						</div>
					</article>
					<article class="media-split">
						<img src="images/criminalization.png" alt="Parallels of Mass Incarceration and Mass Detention workshop screen shot" class="media-split__img shadow rounded">
						<div class="media-split__info">
							<header role="banner">
								<h2>Parallels of Mass Incarceration and Mass Detention</h2>
								<h6>45 Minutes</h6>
							</header>
							<p>This workshop is to access the parallels of mass incarceration and mass detention on stolen land. The public safety narrative has manipulated the American people to adopt racist practices that have caged millions of people. Caging people for profit and labor are acts of violence against humanity. US history paints a clear picture of the targeted abuse towards Black and Brown communities. Our objective is to walk through the parallels you cannot unsee.</p>
						</div>
					</article>
					<article class="media-split media-split--reverse">
						<img src="images/border-imperialism.png" alt="Border Imperialism in Our Everyday Lives workshop screen shot" class="media-split__img shadow rounded">
						<div class="media-split__info">
							<header role="banner">
								<h2>Border Imperialism in Our Everyday Lives</h2>
								<h6>50 Minutes</h6>
								<h4>Interactive Session</h4>
							</header>
							<p>This workshop will teach you the underlinings of Border Imperialism and we will collectively unpack every section in terms of experience, history, ideas, and examples. It covers:</p>
							<ul>
								<li>The Displacement of People</li>
								<li>Exploitation</li>
								<li>Entrenchment of Racialized Hierarchy</li>
								<li>Criminalization</li>
							</ul>
							<p>This is an interactive section that will be facilitated as a large group or small group. It consists of a 10 minute introduction, a 30 minute activity, and 10 minutes of Q&amp;A.</p>
						</div>
					</article>
					<article class="media-split">
						<img src="images/puzzle.jpg" alt="Parallels of Mass Incarceration and Mass Detention workshop screen shot" class="media-split__img shadow rounded">
						<div class="media-split__info">
							<header role="banner">
								<h2>A Process of Elimination</h2>
								<h6>1 Hour</h6>
								<h4>Interactive Session</h4>
							</header>
							<p>I’ve done this interactive session with thousands of people! Ranging from all ages, backgrounds, identities. I’ve never given it a name until now but I’ve seen this be effective in every setting I’ve done this in. We start with identifying 4 key components of our lives and unpacking the importance of those. One by one we start eliminating our chosen key components and learning the true importance of sharing those with others. Prepare to get a little more acquainted with your team. 10 minute introduction, 45 minutes of activity and 10 minutes of closing remarks and Q&A.</p>
							<p><strong>This session can be included with all workshops by request.</strong></p>
						</div>
					</article>
					<article class="media-split media-split--reverse">
						<svg class="media-split__img d-none d-md-block" preserveaspectratio="xMidYMid" viewbox="0 0 100 100" xmlns:xlink="http://www.w3.org/1999/xlink" xmlns="http://www.w3.org/2000/svg">
						<g transform="rotate(0 50 50)">
							<rect fill="#5b0ce2" height="12" rx="3" ry="6" width="6" x="47" y="24">
								<animate attributename="opacity" begin="-1.8333333333333333s" dur="2s" keytimes="0;1" repeatcount="indefinite" values="1;0"></animate>
							</rect>
						</g>
						<g transform="rotate(30 50 50)">
							<rect fill="#5b0ce2" height="12" rx="3" ry="6" width="6" x="47" y="24">
								<animate attributename="opacity" begin="-1.6666666666666667s" dur="2s" keytimes="0;1" repeatcount="indefinite" values="1;0"></animate>
							</rect>
						</g>
						<g transform="rotate(60 50 50)">
							<rect fill="#5b0ce2" height="12" rx="3" ry="6" width="6" x="47" y="24">
								<animate attributename="opacity" begin="-1.5s" dur="2s" keytimes="0;1" repeatcount="indefinite" values="1;0"></animate>
							</rect>
						</g>
						<g transform="rotate(90 50 50)">
							<rect fill="#5b0ce2" height="12" rx="3" ry="6" width="6" x="47" y="24">
								<animate attributename="opacity" begin="-1.3333333333333333s" dur="2s" keytimes="0;1" repeatcount="indefinite" values="1;0"></animate>
							</rect>
						</g>
						<g transform="rotate(120 50 50)">
							<rect fill="#5b0ce2" height="12" rx="3" ry="6" width="6" x="47" y="24">
								<animate attributename="opacity" begin="-1.1666666666666667s" dur="2s" keytimes="0;1" repeatcount="indefinite" values="1;0"></animate>
							</rect>
						</g>
						<g transform="rotate(150 50 50)">
							<rect fill="#5b0ce2" height="12" rx="3" ry="6" width="6" x="47" y="24">
								<animate attributename="opacity" begin="-1s" dur="2s" keytimes="0;1" repeatcount="indefinite" values="1;0"></animate>
							</rect>
						</g>
						<g transform="rotate(180 50 50)">
							<rect fill="#5b0ce2" height="12" rx="3" ry="6" width="6" x="47" y="24">
								<animate attributename="opacity" begin="-0.8333333333333334s" dur="2s" keytimes="0;1" repeatcount="indefinite" values="1;0"></animate>
							</rect>
						</g>
						<g transform="rotate(210 50 50)">
							<rect fill="#5b0ce2" height="12" rx="3" ry="6" width="6" x="47" y="24">
								<animate attributename="opacity" begin="-0.6666666666666666s" dur="2s" keytimes="0;1" repeatcount="indefinite" values="1;0"></animate>
							</rect>
						</g>
						<g transform="rotate(240 50 50)">
							<rect fill="#5b0ce2" height="12" rx="3" ry="6" width="6" x="47" y="24">
								<animate attributename="opacity" begin="-0.5s" dur="2s" keytimes="0;1" repeatcount="indefinite" values="1;0"></animate>
							</rect>
						</g>
						<g transform="rotate(270 50 50)">
							<rect fill="#5b0ce2" height="12" rx="3" ry="6" width="6" x="47" y="24">
								<animate attributename="opacity" begin="-0.3333333333333333s" dur="2s" keytimes="0;1" repeatcount="indefinite" values="1;0"></animate>
							</rect>
						</g>
						<g transform="rotate(300 50 50)">
							<rect fill="#5b0ce2" height="12" rx="3" ry="6" width="6" x="47" y="24">
								<animate attributename="opacity" begin="-0.16666666666666666s" dur="2s" keytimes="0;1" repeatcount="indefinite" values="1;0"></animate>
							</rect>
						</g>
						<g transform="rotate(330 50 50)">
							<rect fill="#5b0ce2" height="12" rx="3" ry="6" width="6" x="47" y="24">
								<animate attributename="opacity" begin="0s" dur="2s" keytimes="0;1" repeatcount="indefinite" values="1;0"></animate>
							</rect>
						</g>
						</svg>
						<div class="media-split__info">
							<header role="banner">
								<h2>Coming Soon</h2>
								<h5>Workshops under development</h5>
							</header>
							<h4>Assimilation, Media &amp; Hxstory</h4>
							<h4>Consumerism, Immigration, &amp; Globalization</h4>
							<h4>COVID-19 &amp; Immigration</h4>
						</div>
					</article>
				</section>
			</main>
			<?php
			return $html.(trim(ob_get_clean())).self::getFooterHtml();
		}
		/**
		 * Writes the Index Page to disk
		 * @return null
		 */
		protected function writeIndexPage() {
			$filename = 'index.html';
			file_put_contents($filename, self::getIndexHtml());
			$this->pages[] = $filename;
			return $filename;
		}
		/**
		 * Writes the About Page to disk
		 * @return null
		 */
		protected function writeAboutPage() {
			$filename = 'about.html';
			file_put_contents($filename, self::getAboutHtml());
			$this->pages[] = $filename;
			return $filename;
		}
		/**
		 * Writes the About Page to disk
		 * @return null
		 */
		protected function writeHirePage() {
			$filename = 'hire.html';
			file_put_contents($filename, self::getHireHtml());
			$this->pages[] = $filename;
			return $filename;
		}
		/**
		 * Gets HTML for single episode card
		 * @param \SimpleXMLElement $episode Single Episode
		 * @param int $episode_num number of episode
		 * @return string
		 */
		protected function getEpisodeCardHtml(\SimpleXMLElement $episode, int $episode_num) {
			ob_start();
			?>
			<div class="card">
				<div class="card-body">
					<h4 class="card-title" title="<?=$episode->title?>"><?=$episode->title?></h4>
					<h6 class="card-subtitle mb-2 text-muted" title="<?=$episode->xpath('itunes:subtitle')[0]?>s"><?=$episode->xpath('itunes:subtitle')[0]?> (<?=$episode->xpath('itunes:duration')[0]?>)</h6>
					<h6 class="blog-date"><?=date('F j, Y',strtotime($episode->pubDate))?></h6>
					<p class="card-text"><?=$episode->description?></p>
					<a href="<?=self::getEpisodeFilename($episode_num)?>" class="btn btn-primary">Episode Page</a>
				</div>
			</div>
			<?php
			return trim(ob_get_clean());
		}
		/**
		 * Gets HTML for a row of cards
		 * @param array $cards HTML of each card in the row
		 * @param int $page_num Page number for card row
		 * @return string
		 */
		public static function getEpisodeCardRowHtml(array $cards, int $page_num) {
			if($page_num < 1) {
				throw new \InvalidArgumentException("page_num must be positive");
			}
			$offset = ($page_num * self::EPISODES_PER_PAGE) - self::EPISODES_PER_PAGE;
			ob_start();
			?>
			<div id="episodes-container-<?=$page_num?>" class="episode-container center" style="display:none;" data-pagenum="<?=$page_num?>">
				<?php
				foreach($cards as $i => $card) {
					echo $card;
				}
				?>
			</div>
			<?php
			return trim(ob_get_clean());
		}
		/**
		 * Gets pagination HTML
		 * @param int $num_pages Number of pages to include
		 * @return string
		 */
		protected function getEpisodePagePagination(int $num_pages) {
			ob_start();
			?>
			<nav aria-label="Episodes Page Selector">
				<ul class="pagination justify-content-center">
					<li class="page-item">
						<a id="prev-page" class="page-link" href="#" ≈aria-label="Previous">
							<span aria-hidden="true">&laquo;</span>
							<span class="sr-only">Previous</span>
						</a>
					</li>
					<?php
					for($i=1;$i<=$num_pages;$i++) {
						?><li class="page-item"><a class="page-link page-num" href="#<?=$i?>"><?=$i?></a></li><?php
					}
					?>
					<li class="page-item">
						<a id="next-page" class="page-link" href="#" aria-label="Next">
							<span aria-hidden="true">&raquo;</span>
							<span class="sr-only">Next</span>
						</a>
					</li>
				</ul>
			</nav>
			<?php
			return trim(ob_get_clean());
		}
		/**
		 * Gets main HTML for episodes page
		 * @return string
		 */
		protected function getAllEpisodeCardsHtml() {
			$cards = [];
			$items = $this->site_sxe->xpath('//item');
			$count = count($items) -1;
			foreach(array_reverse($items) as $item) {
				$cards[] = self::getEpisodeCardHtml($item, --$count);
			}
			$pages = array_chunk($cards,self::EPISODES_PER_PAGE);
			$cards_html = '';
			foreach($pages as $page_num => $cards) {
				$cards_html .= self::getEpisodeCardRowHtml($cards,$page_num+1);
			}
			$html = '';
			ob_start();
			?>
			<main class="episodes-page">
				<h1 class="main-heading">Episodes</h1>
				<div id="episodes-container" class="row justify-content-center">
					<div class="col-lg-6">
						<?=$cards_html?>
						<?=self::getEpisodePagePagination(count($pages))?>
					</div>
				</div>
			</main>
			<?php
			return self::getHeaderHtml("Episodes".' - A Shot of Truth Podcast', 'https://www.shotoftruthpodcast.com/podcasts').trim(ob_get_clean()).self::getFooterHtml();
		}
		/**
		 * Writes the Episodes page to disk
		 * @return string Name of file written
		 */
		protected function writeEpisodeListPage() {
			$filename = self::getPodcastsFilename(true);
			file_put_contents($filename, self::getAllEpisodeCardsHtml());
			$this->pages[] = $filename;
			return $filename;
		}
	}
}