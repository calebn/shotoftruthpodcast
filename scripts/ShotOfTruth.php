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
								preload="none"
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
				<header role="banner" class="jumbotron">
					<div class="container">
						<div class="row justify-content-center">
							<div class="col-lg-8">
								<h1 class="display-3">A Shot Of Truth</h1>
								<h2>With Victoria Matey</h2>
								<p>A podcast focused on celebrating story-telling for and knowledge-sharing with Undocumented people and those impacted by borders.</p>
								<p>Host Victoria Matey Mendoza (she/her) is a queer, undocumented digital creator based in Washington. She has been speaking for eight years on immigration topics and started Shot of Truth Podcast to connect with others and to heal through dialogue. Her vision is to re-imagine a society without borders and cages--by pushing for a collective shift in consciousness.</p>
								<p>Through the podcast, she has been able to connect with people from all over the country who have stories and idea’s to share. Shot of Truth podcast has built a national network of people driven to make change in their communities. You can listen to all of our episodes via Spotify, Apple Podcast, and right here on our website. Workshops are also offered including interactive sessions via Zoom or in person. Learn how you or your organization can collaborate with Shot of Truth Podcast.</p>
								<a class="btn btn--primary" target="_blank" href="https://docs.google.com/forms/d/e/1FAIpQLSeESfcvgr0gbVpZUy26ldApf_ftA-vwdlnsFe0X4SgsJ4unzA/viewform?usp=sf_link" class="button">Work with us</a>
							</div>
						</div>
					</div>
				</header>
				<section class="container section-wide">
					<div class="row">
						<div class="col-md-4">
							<div class="card m-1" >
							<img class="card-img-top" src="images/where-to-listen.jpg" alt="Black and white image of Vicky smiling">
								<div class="card-body">
									<h5 class="card-title">Where to Listen</h5>
									<h6 class="card-subtitle mb-2 text-muted">Subscribe to the Podcast</h6>
									<p class="card-text">You can find our podcast at the following places.</p>
									<ul>
										<li><a href="https://itunes.apple.com/us/podcast/a-shot-of-truth/id1436122328" class="card-link">Apple Podcasts</a></li>
										<li><a href="https://open.spotify.com/show/014rxomsYCAhWX98U1OMzk?si=jc7U--UbRIS0MZlCiL5vYA" class="card-link">Spotify</a></li>
										<li><a href="https://podcasts.google.com/feed/aHR0cHM6Ly9zaG90b2Z0cnV0aHBvZGNhc3QuY29tL3Nob3RvZnRydXRocG9kY2FzdHJzcy54bWw" class="card-link">Google Podcasts</a></li>
										<li><a href="https://www.stitcher.com/s?fid=235842&refid=stpr" class="card-link">Stitcher</a></li>
										<li><a href="https://soundcloud.com/sudo-science/sets/a-shot-of-truth-podcast" class="card-link">SoundCloud</a></li>
										<li><a href="/shotoftruthpodcastrss.xml" class="card-link">RSS Feed</a></li>
									</ul>
								</div>
							</div>
						</div>
						<div class="col-md-4">
							<div class="card m-1" >
							<img class="card-img-top" src="images/join-the-cause.jpg" alt="Table with colorful blanket, flowers and candles">
								<div class="card-body">
									<h5 class="card-title">Join The Cause</h5>
									<h6 class="card-subtitle mb-2 text-muted">Help Us Reach More People</h6>
									<p class="card-text">You can become a contributing member. All funds goes directly to us, the podcasters and it's creation.</p>
									<a href="https://www.patreon.com/shotoftruthpodcast" class="card-link">Donate on Patreon</a>
								</div>
							</div>
						</div>
						<div class="col-md-4">
							<div class="card m-1" >
							<img class="card-img-top" src="images/reach-out.jpg" alt="Group of smiling people">
								<div class="card-body">
									<h5 class="card-title">Reach Out</h5>
									<h6 class="card-subtitle mb-2 text-muted">Join the Community</h6>
									<p class="card-text">Follow, retweet, say hi. Give us feedback, ask us questions or tell us your story. We'd love to get to know you.</p>
									<ul>
										<li><a href="https://www.instagram.com/shotoftruthpodcast/" class="card-link">Instagram</a></li>
										<li><a href="https://www.facebook.com/ShotOfTruthPodcast" class="card-link">Facebook</a></li>
										<li><a href="https://twitter.com/shotoftruthpod" class="card-link">Twitter</a></li>
										<li><a href="https://www.tiktok.com/@shotoftruthpodcast" class="card-link">TikTok</a></li>
										<li><a href="mailto:info@shotoftruthpodcast.com" class="card-link">Email</a></li>
									</ul>
								</div>
							</div>
						</div>
					</div>
				</secti>
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
								<h1 class="super">About</h1>
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
					<h1 class="super">Hire</h1>
					<p>Victoria has been speaking for eight years on unpacking systemic and parallel issues to immigration. She has participated in national leadership, education and technology conferences and has partnered with various businesses and organizations. She offers workshops and works with other presenters to bring a deeper understanding of our role in all the intersecting pieces of migration and more.</p>
					<p>All of our services are offered on a sliding scale. Please contact <a href="mailto:info@shotoftruthpodcast.com">info@shotoftruthpodcast.com</a>.com to book Victoria or learn more!</p>
				</header>
				<section class="section-wide">
				<div id="carouselExampleIndicators" class="carousel slide" data-ride="carousel">
					<div class="carousel-inner">
						<div class="carousel-item active">
							<div class="carousel-item__container d-block w-100 rounded dark-bg bg-primary-indigo">
								<h1 class="u-text-center">Workshops</h1>
								<div class="carousel-item__content">
									<h5><a href="#climate-change-and-migration">Climate Change and Migration</a></h5>
									<h5><a href="#parallels-of-mass-incarceration-and-mass-detention">Parallels of Mass Incarceration and Mass Detention</a></h5>
									<h5><a href="#undocumented-students-and-higher-education">Undocumented Students & Higher Education</a></h5>
									<h5><a href="#criminalization-exploitation-and-disposal-of-immigrants">The Criminalization Exploitation and Disposal of Immigrants</a></h5>
									<h5><a href="#a-process-of-elimination">A Process of Elimination</a></h5>
									<h5><a href="#border-imperialism">Border Imperialism in our Everyday Lives</a></h5>
								</div>
							</div>
						</div>
						<div class="carousel-item">
							<div class="carousel-item__container d-block w-100 rounded dark-bg bg-primary-indigo">
								<h1 class="u-text-center">Sponsored Episodes</h1>
								<h4 class="u-text-center">Our values: Community, Authenticity, Compassion</h4>
								<div class="carousel-item__content">
									<p class="bold">You can build episodes with us! Does your organization want to highlight a deeper understanding of the work you do with immigrant communities? We work with you to share information, creative solutions, and more. We have partnered with community centers and nonprofits in the past to create episodes like “El Centro y El Census”--an episode focused on highlighting a Latino Center in Tacoma, WA helping immigrants learn about the census.</p>
								</div>
							</div>
						</div>
						<div class="carousel-item">
							<div class="carousel-item__container d-block w-100 rounded dark-bg bg-primary-indigo">
								<h1 class="u-text-center">Live Ad Reads</h1>
								<div class="carousel-item__content">
									<p class="bold">We highlight your events, businesses, or programs at the beginning of our show. These partnerships need to be scheduled 2-3 weeks prior to your event or program. We want to highlight and work with organizations actively doing immigration, organizing, or education work. Please contact us for rates at <a href="mailto:info@shotoftruthpodcast.com">info@shotoftruthpodcast.com</a></p>
								</div>
							</div>
						</div>
						<div class="carousel-item">
							<div class="carousel-item__container d-block w-100 rounded dark-bg bg-primary-indigo">
								<h1 class="u-text-center">Podcast Consulting</h1>
								<h4 class="u-text-center">(Coming Soon)</h4>
								<div class="carousel-item__content">
									<div class="two-up">
										<div class="two-up__item">
											<p class="bold">Want to start your own podcast? We are here to help. This package includes a total of four sessions and two episode production. You get to work personally with our team to build your podcast. This is a month-long commitment and requires highly invested people ready to take an idea off the ground. We will coach you through the first production of your show and prepare you to run your podcast moving forward.</p>
										</div>
										<div class="two-up__item">
											<h5>Sessions Offered:</h5>
											<ul>
												<li>Starting up your Podcast 101</li>
												<li>Technology: Virtual or in person</li>
												<li>Podcast Episode Prep &amp; Marketing</li>
												<li>Tracking &amp; Monetizing</li>
												<li>Debrief Session</li>
											</ul>
										</div>
									</div>
									
								</div>
							</div>
						</div>
					</div>
					<a class="carousel-control-prev" href="#carouselExampleIndicators" role="button" data-slide="prev">
						<span class="carousel-control-prev-icon" aria-hidden="true"></span>
						<span class="sr-only">Previous</span>
					</a>
					<a class="carousel-control-next" href="#carouselExampleIndicators" role="button" data-slide="next">
						<span class="carousel-control-next-icon" aria-hidden="true"></span>
						<span class="sr-only">Next</span>
					</a>
				</div>
				</section>
				<section class="section-wide">
					<article id="climate-change-and-migration" class="media-split">
						<img src="images/climate-change-and-migration.png" alt="Climate change and migration presentation slide" class="media-split__img shadow rounded" width="960" height="540">
						<div class="media-split__info">
							<header role="banner">
								<h2>Climate Change and Migration</h2>
								<h6>1 Hour</h6>
							</header>
							<p>This workshop highlights a global perspective of the impacts of climate change and migration. By 2050 over 200 million to a billion people will be displaced by climate catastrophes. As we view migration as a global issue we must look at climate change through the same lens, it is a global crisis with only a collective solution.</p>
							<p>I have spent years working on immigration topics and this is the first time I look at the intersections of climate change. I cannot continue my work without talking about this. We should all be preparing for massive migration in a just way.</p>
						</div>
					</article>
					<article id="criminalization-exploitation-and-disposal-of-immigrants" class="media-split media-split--reverse">
						<img src="images/public-safety-narrative.png" alt="The Criminalization, Exploitation, and Disposal of Immigrants presentation screen shot" class="media-split__img shadow rounded" width="960" height="540">
						<div class="media-split__info">
							<header role="banner">
								<h2>The Criminalization, Exploitation, and Disposal of Immigrants</h2>
								<h6>1.5 Hours</h6>
							</header>
							<p>The Criminalization, Exploitation, and Disposal of Immigrants in terms of history, policy, and the media. This is an extended version of my first <a href="https://www.youtube.com/watch?v=mHr2MoAhSE0" target="_blank">TEDx Talk</a>. We have to look at language, policies, institutions to understand the structural system racism forced upon immigrants and undocumented immigrants.</p>
							<p>Furthering my research on exploitation and detention this workshop will advance your understanding of the ideologies and institutions that criminalize, exploit, and dispose of immigrants. This workshop will help you view those ideologies and institutions through a different lens to better critically understand the context of US history and undocumented immigrants.</p>
						</div>
					</article>
					<article id="undocumented-students-and-higher-education" class="media-split">
						<img src="images/undocumented-students-and-higher-education.png" alt="Undocumented students and higher education presentation slide" class="media-split__img shadow rounded" width="960" height="540">
						<div class="media-split__info">
							<header role="banner">
								<h2>Undocumented Students &amp; Higher Education</h2>
								<h6>2 Hours</h6>
								<h4>For Educators &amp; Student Affairs Professionals</h4>
							</header>
							<p>This workshop is catered to educators and student affairs professionals. It will highlight the importance of being aware and conscious of the undocumented experience in higher education. It will cover a brief explanation of state financial support systems and DACA but heavily focuses on our own biases, internalized racism, and how to better support undocumented students.</p>
							<p>I will share pieces of my student experience in higher education and additionally my work as a student affairs professional. This is not a how-to step by step but rather turning inwards with your team and accessing your power in making a difference in your institution for and with undocumented students</p>
						</div>
					</article>
					<article id="parallels-of-mass-incarceration-and-mass-detention" class="media-split media-split--reverse">
						<img src="images/parallels-of-mass-incarceration-and-mass-detention.png" alt="Paralles of mass incarceration and mass detention presentation screen shot" class="media-split__img shadow rounded" width="960" height="540">
						<div class="media-split__info">
							<header role="banner">
								<h2>Parallels of Mass Incarceration and Mass Detention</h2>
								<h6>45 Minutes</h6>
							</header>
							<p>This workshop is to assess the parallels of mass incarceration and mass detention. The public safety narrative has manipulated the American people to adopt racist practices that have caged millions of people. Caging people for profit and labor are acts of violence against humanity. US history paints a clear picture of the targeted abuse towards Black and Brown communities. Our objective is to walk through the parallels you cannot unsee.</p>
							<p>This workshop is presented by Victoria Matey and Steven Simmons.</p>
						</div>
					</article>
					<article id="a-process-of-elimination" class="media-split">
						<img src="images/process-of-elimination.png" alt="A process of elimination presentation slide" class="media-split__img shadow rounded" width="960" height="540">
						<div class="media-split__info">
							<header role="banner">
								<h2>A Process of Elimination</h2>
								<h6>1 Hour</h6>
								<h4>Interactive Session</h4>
							</header>
							<p>I’ve done this interactive session with thousands of people! Ranging from all ages, backgrounds, identities. I’ve never given it a name until now but I’ve seen this be effective in every setting I’ve done this in. We start with identifying 4 key components of our lives and unpacking the importance of those. One by one we start eliminating our chosen key components and learning the true importance of sharing those with others. Prepare to get a little more acquainted with your team.</p>
							<ul>
								<li>10 minute introduction</li>
								<li>45 minutes of activity</li>
								<li>10 minutes of closing remarks and Q&amp;A.</li>
							</ul>
							<p><strong>This session can be included with all workshops by request.</strong></p>
						</div>
					</article>
					<article id="border-imperialism" class="media-split media-split--reverse">
						<img src="images/border-imperialism.png" alt="Border Imperialism presentation slide" class="media-split__img shadow rounded" width="960" height="540">
						<div class="media-split__info">
							<header role="banner">
								<h2>Border Imperialism in Our Everyday Lives</h2>
								<h6>50 Minutes</h6>
								<h4>Interactive Session</h4>
							</header>
							<p>This workshop will teach you the underlinings of Border Imperialism and we will collectively unpack every section in terms of experience, history, ideas, and examples. As follows: The Displacement of People, Exploitation, Entrenchment of Racialized Hierarchy, and Criminalization. This is an interactive section that will be facilitated as a large group or small group.</p>
							<ul>
								<li>10 minute introduction</li>
								<li>30 minutes of activity</li>
								<li>10 minutes of closing remarks and Q&amp;A.</li>
							</ul>
							<p><strong>This session can be included with all workshops by request.</strong></p>
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
					<a href="<?=self::getEpisodeFilename($episode_num)?>" class="btn btn--primary">Episode Page</a>
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