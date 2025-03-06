START TRANSACTION;

CREATE TABLE `config_classes` (
  `CLASS_ID` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `CLASS_NAME` varchar(100) DEFAULT NULL,
  `CLASS_SCHOOL_ID` varchar(20) DEFAULT NULL,
  `CLASS_GRADE` int(11) DEFAULT NULL,
  `CLASS_TEACHER_ID` varchar(20) DEFAULT NULL,
  `CLASS_CREATED_AT` datetime DEFAULT NULL,
  `CLASS_CREATED_BY` varchar(20) DEFAULT NULL,
  `CLASS_MODIFIED_ON` datetime DEFAULT NULL,
  `CLASS_MODIFIED_BY` varchar(20) DEFAULT NULL
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb3 COLLATE = utf8mb3_unicode_ci;

CREATE TABLE `config_menu` (
  `CM_ID` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `CM_MENU_NAME` varchar(20) DEFAULT NULL,
  `CM_MENU_ITEM` varchar(50) DEFAULT NULL,
  `CM_MENU_VALUE` varchar(50) DEFAULT NULL,
  `CM_SORT` int(11) DEFAULT NULL
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb3 COLLATE = utf8mb3_unicode_ci;

INSERT INTO
  `config_menu` (
    `CM_ID`,
    `CM_MENU_NAME`,
    `CM_MENU_ITEM`,
    `CM_MENU_VALUE`,
    `CM_SORT`
  )
VALUES
  (1, 'STATUS', 'Active', 'ACTIVE', 1),
  (2, 'STATUS', 'Inactive', 'INACTIVE', 2),
  (3, 'STATUS', 'Draft', 'Draft', 3),
  (4, 'PLANNING', '0', '0', 0),
  (5, 'PLANNING', '1', '1', 1),
  (6, 'PLANNING', '2', '2', 2),
  (7, 'PLANNING', '3', '3', 3),
  (8, 'PLANNING', '4', '4', 4);

CREATE TABLE `config_pupils` (
  `PUPIL_ID` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `PUPIL_CLASSID` int(11) DEFAULT NULL,
  `PUPIL_STUDENTID` int(11) DEFAULT NULL,
  `PUPIL_CREATED_AT` datetime DEFAULT NULL,
  `PUPIL_CREATED_BY` varchar(20) DEFAULT NULL,
  `PUPIL_MODIFIED_ON` datetime DEFAULT NULL,
  `PUPIL_MODIFIED_BY` varchar(20) DEFAULT NULL
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb3 COLLATE = utf8mb3_unicode_ci;

CREATE TABLE `config_schools` (
  `SCHOOL_ID` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `SCHOOL_NAME` varchar(100) DEFAULT NULL,
  `SCHOOL_SN` varchar(20) DEFAULT NULL,
  `SCHOOL_CONTACT` varchar(60) DEFAULT NULL,
  `SCHOOL_STATUS` varchar(10) DEFAULT NULL,
  `SCHOOL_CREATED_AT` datetime DEFAULT NULL,
  `SCHOOL_CREATED_BY` varchar(20) DEFAULT NULL,
  `SCHOOL_MODIFIED_ON` datetime DEFAULT NULL,
  `SCHOOL_MODIFIED_BY` varchar(20) DEFAULT NULL
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb3 COLLATE = utf8mb3_unicode_ci;

CREATE TABLE `config_users` (
  `USER_ID` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `USER_CODE` varchar(20) DEFAULT NULL,
  `USER_LEVEL` varchar(20) DEFAULT NULL,
  `USER_STATUS` varchar(10) DEFAULT NULL,
  `USER_ORGANIZATION` varchar(80) DEFAULT NULL,
  `USER_LAST_NAME` varchar(50) DEFAULT NULL,
  `USER_FIRST_NAME` varchar(50) DEFAULT NULL,
  `USER_EMAIL` varchar(100) DEFAULT NULL,
  `USER_AUTHORITY` varchar(100) DEFAULT NULL,
  `USER_PASSWORD` varchar(250) DEFAULT NULL,
  `USER_TIMEOUT` int(11) DEFAULT NULL,
  `USER_CREATED_AT` datetime DEFAULT NULL,
  `USER_CREATED_BY` varchar(20) DEFAULT NULL,
  `USER_MODIFIED_ON` datetime DEFAULT NULL,
  `USER_MODIFIED_BY` varchar(20) DEFAULT NULL,
  `USER_SALT` TEXT DEFAULT NULL
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb3 COLLATE = utf8mb3_unicode_ci;

CREATE TABLE `man_sessions` (
  `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `session_public_id` varchar(40) DEFAULT NULL,
  `session_name` varchar(10) DEFAULT NULL,
  `session_start_time` datetime DEFAULT NULL,
  `session_last_updated` datetime DEFAULT NULL,
  `session_end_time` datetime DEFAULT NULL,
  `session_ip` varchar(15) DEFAULT NULL,
  `session_userid` varchar(20) DEFAULT NULL
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb3 COLLATE = utf8mb3_unicode_ci;

CREATE TABLE `quiz` (
  `Q_ID` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `Q_PROMPT_ID` int(11) DEFAULT NULL,
  `Q_PROMPT_TITLE` varchar(50) DEFAULT NULL,
  `Q_STUDENT_ID` varchar(20) DEFAULT NULL,
  `Q_START_TIME` datetime DEFAULT NULL,
  `Q_END_TIME` datetime DEFAULT NULL,
  `Q_DURATION` time DEFAULT NULL,
  `Q_COMPLETED` varchar(1) DEFAULT NULL,
  `Q_TYPING` text DEFAULT NULL,
  `Q_ESSAY` text DEFAULT NULL,
  `Q_WORD_COUNT` int(11) DEFAULT NULL,
  `Q_SENTENCE_COUNT` int(11) DEFAULT NULL,
  `Q_WORD_ERROR` int(11) DEFAULT NULL,
  `Q_SENTENCE_ERROR` int(11) DEFAULT NULL,
  `Q_CIWS` int(11) DEFAULT NULL,
  `Q_WORD_ACCURACY` decimal(10, 3) DEFAULT NULL,
  `Q_SENTENCE_ACCURACY` decimal(10, 3) DEFAULT NULL,
  `Q_WORD_COMPLEXITY` decimal(10, 3) DEFAULT NULL,
  `Q_SENTENCE_COMPLEXITY` decimal(10, 3) DEFAULT NULL,
  `Q_SCORING` MEDIUMTEXT DEFAULT NULL,
  `Q_ESSAY_NOTES` text DEFAULT NULL,
  `Q_TYPING_NOTES` text DEFAULT NULL,
  `Q_TYPE_WORD_COUNT` int(11) DEFAULT NULL,
  `Q_CHARACTER_COUNT` int(11) DEFAULT NULL,
  `Q_TYPING_CORRECT` int(11) DEFAULT NULL,
  `Q_TYPING_WORDS` int(11) DEFAULT NULL,
  `Q_TYPING_CHARS` int(11) DEFAULT NULL,
  `Q_PLANNING` varchar(2) DEFAULT NULL,
  `Q_GRADING_STATUS` varchar(16) DEFAULT NULL,
  `Q_GRADER_ID` varchar(20) DEFAULT NULL,
  `Q_TIDE` MEDIUMTEXT DEFAULT NULL,
  `Q_TIDE_SCORING` text DEFAULT NULL,
  `Q_TIDE_T` varchar(4) DEFAULT NULL,
  `Q_TIDE_I` varchar(4) DEFAULT NULL,
  `Q_TIDE_D` varchar(4) DEFAULT NULL,
  `Q_TIDE_E` varchar(4) DEFAULT NULL,
  `Q_TIDE_C` varchar(4) DEFAULT NULL,
  `Q_CREATED_AT` datetime DEFAULT NULL,
  `Q_VERSION` int(11) DEFAULT NULL,
  `Q_VER_QID` int(11) DEFAULT NULL,
  `Q_CREATED_BY` varchar(20) DEFAULT NULL,
  `Q_MODIFIED_ON` datetime DEFAULT NULL,
  `Q_MODIFIED_BY` varchar(20) DEFAULT NULL,
  `Q_TOKEN_CORRECT` int(11) UNSIGNED DEFAULT NULL,
  `Q_TOKEN_WORD` int(11) UNSIGNED DEFAULT NULL,
  `Q_TOKEN_SEN_INACC` int(11) UNSIGNED DEFAULT NULL,
  `Q_TOKEN_SEN_OVERLAP` int(11) UNSIGNED DEFAULT NULL,
  `Q_TOKEN_SEN_NMAE` int(11) UNSIGNED DEFAULT NULL
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb3 COLLATE = utf8mb3_unicode_ci;

CREATE TABLE `quiz_prompts` (
  `PROMPT_ID` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `PROMPT_SHORT_TITLE` varchar(40) DEFAULT NULL,
  `PROMPT_TITLE` varchar(200) DEFAULT NULL,
  `PROMPT_BODY` text DEFAULT NULL,
  `PROMPT_AUDIO_PROMPT` varchar(100) DEFAULT NULL,
  `PROMPT_AUDIO_PASSAGE` varchar(100) DEFAULT NULL,
  `PROMPT_SOURCE` varchar(100) DEFAULT NULL,
  `PROMPT_INSTRUCTIONS` text DEFAULT NULL,
  `PROMPT_STATUS` varchar(10) DEFAULT NULL,
  `PROMPT_AUDIO_LEN` int(11) DEFAULT NULL,
  `PROMPT_CREATED_AT` datetime DEFAULT NULL,
  `PROMPT_CREATED_BY` varchar(20) DEFAULT NULL,
  `PROMPT_MODIFIED_ON` datetime DEFAULT NULL,
  `PROMPT_MODIFIED_BY` varchar(20) DEFAULT NULL
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb3 COLLATE = utf8mb3_unicode_ci;

INSERT INTO
  `quiz_prompts` (
    `PROMPT_ID`,
    `PROMPT_SHORT_TITLE`,
    `PROMPT_TITLE`,
    `PROMPT_BODY`,
    `PROMPT_AUDIO_PROMPT`,
    `PROMPT_AUDIO_PASSAGE`,
    `PROMPT_SOURCE`,
    `PROMPT_INSTRUCTIONS`,
    `PROMPT_STATUS`,
    `PROMPT_AUDIO_LEN`,
    `PROMPT_CREATED_AT`,
    `PROMPT_CREATED_BY`,
    `PROMPT_MODIFIED_ON`,
    `PROMPT_MODIFIED_BY`
  )
VALUES
  (
    2,
    'edible wrappers',
    'Here\'s a Food Wrapper You Can Eat',
    'Consider the cheese stick. It is not a pretty food. It also isn\'t very healthy. Cheese sticks are about as commonplace as snack food gets. In the packaged version, each cylinder of mozzarella or cheddar is individually wrapped. And, every day, thousands of those little pieces of plastic wrap are thrown in the trash. But maybe not for long.\r\n\r\nTwo researchers at the U.S. Department of Agriculture have made a film. It\'s made from a milk protein. The film can be eaten with the cheese. Which means that it may not be too long before we have a wrapper we can eat. It also could be healthy. Edible plastic exists. But it\'s largely made of starch. It isn\'t protein.\r\n\r\n\"It can be consumed with the food. So it gets rid of one layer of packaging, like with individually wrapped cheese sticks,\" says Peggy Tomasula. She is one of the lead researchers. \"It also gives you the opportunity to add vitamins or minerals or ways to block light damage to the food. And, you can add flavors. If you wanted to add a strawberry flavor to something, you can embed that in the film.\"\r\n\r\nThere is a key factor in the innovative packaging. It is casein. Casein is a group of milk proteins. It has high nutritional value. Tomasula has been researching casein since 2000. She actually created a new version of the protein using carbon dioxide. She noticed that it wasn\'t very soluble in water. That made her believe it might be used to make a film coating that could extend the shelf life of dairy foods.\r\n\r\nTomasula kept exploring the potential of this research. Then another scientist, Laetitia Bonnaillie, joined the USDA team. Tomasula asked her to see if dry milk could be used to produce the film. That would also allow them to make use of surplus milk powder during times when dairy farms are producing too much milk. Bonnaillie also focused on refining the product. She wanted to make it less sensitive to moisture. She wanted to improve the process by which the film was made. She also wanted it to be more uniform and commercial.\r\n\r\nAt the annual meeting of the American Chemical Society, they announced the results of their efforts. It is edible, biodegradable packaging. The casein film could come in sheets. That is not unlike plastic wrap. Or it could be sprayed on as a coating. And, it\'s been found to be significantly more effective at blocking oxygen than ordinary plastic wrap. So it can protect food from spoiling for a much longer time. There would be some limitations at first.\r\n\r\n\"This would mostly be for dairy products or foods that would likely be used with dairy. Like cereal,\" says Tomasula. \"We wouldn\'t put this on fruits and vegetables in a market. You couldn\'t do that because of milk allergies. There would have to be labeling to let people know it\'s milk protein.\"\r\n\r\nAlso, this wouldn\'t mean that all packaging would be eliminated for cheese and other dairy products. They would still need to be covered in some way, in a box or packet to keep the food from getting dirty or exposed to too much moisture. But dispensing with the individual wrapping around each food item could mean a lot less plastic would end up in landfills. By some estimates, it can take as long as 1,000 years for plastic to degrade. And, unfortunately, less than a third of the plastic Americans throw away actually gets recycled.\r\n\r\nThe idea, said Bonnaillie, is to create different versions of the casein film. One might be very soluble, making it better suited for a product you dissolve in water. Another could be considerably less soluble. So it would be more resistant to moisture. It would work better as protective packaging.\r\n\r\n\"We are trying things with the extremes,\" she says. \"We\'ve just started exploring applications. There are many more things we can do.\"\r\n\r\nSay so long to sugar?\r\n\r\nFor instance, instead of tearing open a paper container to make instant coffee or soup, you could just drop a casein packet of the ingredients into water. Everything would dissolve. Plus, extra protein would be added. But food companies might actually prefer a spray version of the product.\r\n\r\n\"That way they could store a mixture of the particular milk proteins in water. And then make the coatings and spray them on when they\'re processing the food,\" says Tomasula.\r\n\r\nOne possibility would be to spray the protein film on cereal. The cereal can be coated with sugar to keep it crunchy.\r\n\r\n\"It could be fat-free.\"?It is a healthier way to replace a process that\'s now largely done with sugar, says Bonnaillie. Tomasula adds: \"We\'re hoping that for something like meal replacement bars we can make the edible wrapping taste like chocolate. We could combine the ingredients together and provide a little more nutrition.\"',
    'wrappers_prompt.m4a',
    'wrappers.mp3',
    'Smithsonian Tween Tribune',
    'Write an informative paper that will help others learn about edible food packaging. Be sure to use information from the article you just read to give reasons why edible wrappers are an exciting development in food packaging.',
    'ACTIVE',
    38,
    '2021-11-02 10:54:51',
    'DacquesV',
    '2022-03-01 01:26:14',
    'DacquesV'
  ),
  (
    3,
    'flies',
    'Swat Up: Six Reasons to Love Flies',
    'They buzz and bother us but have also taught us much about who we are. Here are six reasons to respect, if not love, the fly.\r\n\r\n1. First creatures in space.\r\n\r\nIn February, 1947, American scientists launched a group of fruit flies into space.\r\n\r\nThe flies were sent 42 miles from Earth to study the effects of radiation at high altitude. They were ejected in a container that parachuted to the ground. When they landed scientists found them to be in perfect health.\r\n\r\nFruit flies are still being sent into space to simulate the effects on astronauts. Fruit flies are chosen because humans and fruit flies are surprisingly similar.\r\n\r\n2. Nature\'s detectives.\r\n\r\nFlies help investigators establish the time of death of a body because the various stages of decomposition attract different insects at different times.\r\n\r\nOne of the first insects to settle into a freshly dead body is the blowfly. The blowfly has an acute sense of smell. Their sense of smell allows them to find decomposing matter quickly from miles away.\r\n\r\nFemale blowflies arrive within minutes to lay eggs. These eggs hatch into maggots within 24 hours. Across several days they go through six different stages before becoming fully-grown adult flies.\r\n\r\n3. Flying aces.\r\n\r\nFlies are the fastest insects on the planet.\r\n\r\nHorse flies are the best flyers and can reach 90mph. They have a pair of balance organs near their wings called halteres. Halteres allow horse flies to fly upside down and rotate mid-air.\r\n\r\nSome types of fly don\'t have any wings at all. Up against skills like the horse fly, we should perhaps feel sorry for the species of fly suffering an identity crisis because they can\'t fly.\r\n\r\n4. No flies means no chocolate.\r\n\r\nAll the chocolate we eat comes from the seeds of a tree called the cacao. The cacao has flowers that are small and require tiny pollinators. This is where flies come in.\r\n\r\nChocolate midges are flies that are no bigger than the size of a pinhead. They seem to be the only creatures that can work their way into the flowers to pollinate them.\r\n\r\n5. Natural born recyclers.\r\n\r\nWhether its cow manure or dead bodies, flies recycle waste into the ground. Naturalist Peter Marren says this makes them the most valuable insect.\r\n\r\n\"Without houseflies and bluebottles and other types of fly, we would simply fill up with corpses and other filth. We would drown in filth,\" he says.\r\n\r\n6. Nobel Prize winners.\r\n\r\nFruit flies helped scientists win four Nobel Prizes in Physiology or Medicine.\r\n\r\nIn 1933, Thomas Hunt Morgan won a Nobel Prize for his work on genetic inheritance. He researched mutations in fruit flies. This research led to the theory that genes were carried on chromosomes and passed down through generations.\r\n\r\nHermann J. Muller was awarded a Nobel Prize in 1946 for finding that X-rays can cause genetic mutations in fruit flies.\r\n\r\nIn 1995, Edward B. Lewis, Christiane Nusslein-Volhard and Eric F. Wieschaus won a Nobel Prize for their studies into the genetic control of early embryonic development. They also used fruit flies in their research.\r\n\r\nMost recently, Jules A. Hoffman and Bruce Beutler won a Nobel Prize in 2011 for studying immunity. The researchers used fruit flies because the results could later be applied to humans.',
    'flies_prompt.m4a',
    'flies.mp3',
    'BBC Radio Natural Histories',
    'Write an informative paper that will help others learn about flies. Be sure to use information from the article you just read to give reasons why flies are important to people.',
    'ACTIVE',
    35,
    '2021-11-02 10:54:51',
    'DacquesV',
    '2022-03-01 01:25:49',
    'DacquesV'
  ),
  (
    4,
    'elevated buses',
    'Can an Elevated Bus Solve China\'s Traffic Woes?',
    '\"To say that China has traffic issues is an understatement. A 2015 study revealed that the country is home to the most traffic congested cities in the world. Though Chinese authorities have tried to control the traffic flow, nothing appears to be working. They tried charging tolls for use of busy roads. They also tried adding 50 lanes to a highway. Now, some engineers are proposing an ingenious solution to improve the country\'s traffic woes â€” an elevated bus that glides over cars.\r\n\r\nThe concept for the \"straddling\" or elevated bus was first introduced six years ago. However, while the idea generated a lot of excitement, the bus never became a reality. This was due to safety concerns. Since then, Song Youzhou and his team have been working hard to perfect the design.\r\n\r\nThe new and improved Transit Elevated Bus was unveiled at an exhibition in China. The Transit Elevated Bus is a cross between a subway and a bus. Designed to run on rails, it covers two vehicle lanes. However because the bus is elevated over 6 feet above the road, it allows vehicle traffic to continue flowing beneath it. Retractable ramps enable commuters to board and disembark the bus at bus stops.\r\n\r\nThe Transit Elevated Bus can accommodate up to 1,400 passengers at a time. It can also reach a maximum speed of 37 miles per hour. With these features, the inventors believe that the Transit Elevated Bus is a better and cheaper alternative to subways. According to Youzhou, each elevated bus will cost about 4.5 million dollars. This cost is significantly less than building a new subway.\r\n\r\nAlso, thanks to its large passenger capacity, a single Transit Elevated Bus could replace 40 traditional buses. Besides reducing road congestion, eliminating the traditional buses would help alleviate another big problem the country is grappling with pollution. That\'s because the Transit Elevated Bus is powered by solar energy and electricity, not fossil fuels. Furthermore, Youzhou estimates that replacing 40 buses could reduce the use of fuel by 800 tons. It could also reduce carbon emissions, by 2,500 tons every year!\r\n\r\nThough some critics are still opposed to the idea, officials of one coastal city seem to be willing to give it a try. They commissioned the Transit Elevated Bus team to build a 12-mile track to test the innovative bus. If everything goes according to schedule, the bus will be ready for its first passengers very soon!\"',
    'buses_prompt.m4a',
    'buses.mp3',
    'DOGO News',
    'Write an informative paper that will help others learn about the potential benefits of replacing school buses with transit elevated buses. Be sure to use information from the article you just read to give reasons why replacing the school buses would be beneficial.',
    'ACTIVE',
    42,
    '2021-11-02 10:54:51',
    'DacquesV',
    '2022-03-01 01:26:23',
    'DacquesV'
  ),
  (
    5,
    'plastic bottle village',
    'Plastic Bottle Village',
    'According to experts, over 22,000 plastic bottles are discarded every second, and the numbers are only growing. Though the detrimental impact of plastic on the environment is well-known, consumption of drinks bottled in the most commonly used type of plastic continues to rise at alarming levels. Some of them do get recycled. Most bottles end up in the ocean. In the ocean, the bottles disintegrate into smaller pieces. The small pieces are often mistaken for food by innocent fish and birds.\r\n\r\nNow, Robert Bezeau has come up with an idea that may not solve the world\'s plastic woes. But it may inspire others. He plans to use the plastic soda bottles to build an entire village in the jungles of Panama.\r\n\r\nOriginally from Canada, Bezeau has been living on an island in Panama for many years. He started a recycling project there in 2012. He started noticing plastic waste carelessly tossed on the island\'s beautiful beaches. In just 18 months, Bezeau and his volunteers collected over a million plastic bottles!\r\n\r\nAfter the recycling project ended, Bezeau could not ignore the magnitude of the waste being generated. So in 2015, he came up with the idea of using the bottles to construct homes. Plastic Bottle Village was born. The project is located on the northernmost and main island in Panama. The project is still in its infancy. So far, only one two-story house has been built. But, Bezeau wants to build an entire community. Eventually, he hopes Plastic Bottle Village will have 19 to 20 plastic homes. In addition to home, the community will also include a vegetable garden, a small shop, and an eco-lodge.\r\n\r\nIf he can raise the funds, Bezeau also plans to build an education center. The education center will be a place where others can come and learn how to use the plastic waste more productively. He wants the village to be environmentally responsible to reflect its pristine jungle location.\r\n\r\nEven though the idea to use plastic bottles to make homes is relatively new, it is surprisingly easy to do. The builders begin by constructing a steel frame that mimics the shape of all sides of the house. Then, they fill it with large plastic bottles. They usually use the kind of plastic bottles that contain soft drinks. Each home requires between 10,000 and 25,000 bottles. Smaller homes require fewer bottles and larger homes require more bottles. Once the bottles are in place, necessary services like electricity are installed. Next, the bottle-filled frame is plastered with layers of concrete. Finally, the windows, roof, and septic tank are installed.\r\n\r\nInterestingly, the environmental benefits of using the plastic bottles for construction go beyond reducing the amount of waste in our oceans. As it turns out, the bottles are good insulators. They help keep the home at a comfortable temperature. This alleviates the need for expensive air conditioners. Good insulation is a significant advantage in tropical countries like Panama, where the weather is warm year round. Even better? The homes are also earthquake resistant. This feature is important given that Panama is susceptible to earthquakes.\r\n\r\nWhile these buildings are a smart way to utilize the plastic waste, they are not the solution to our environmental woes. The only way to solve the issue is to reduce the amount of plastic that ends up in the ocean. The smartest thing to do is avoid buying plastic bottles altogether. However, placing plastic bottles in recycling bins will also go a long way in curbing the amount of plastic that ends up in our oceans each year. So be sure to do your part!',
    'village_prompt.m4a',
    'village.mp3',
    'DOGO News',
    'Write an informative paper that will help others learn about building houses out of plastic bottles. Be sure to use information from the article you just read to give reasons why using plastic bottles to build homes would be helpful.',
    'ACTIVE',
    38,
    '2021-11-02 10:54:51',
    'DacquesV',
    '2022-03-01 01:25:59',
    'DacquesV'
  ),
  (
    9,
    'extinctions',
    'How to Speed up Extinctions',
    'It seems like every day there is a story about a polar bear struggling to find food in a melting Artic. Polar bears are just the tip of the iceberg when it comes to animals on the brink of extinction. According to an analysis of 15,000 studies, 1 in 8 plant or animal species may disappear. That means almost 1 million species could join the dinosaurs as creatures of the past.\r\n\r\nWhat\'s behind this surprising finding? People. The number of people on the planet has doubled in the last 50 years. In 1970, there were about 3.7 billion people on the planet, but that number is 7.6 billion today! The activities that have helped humans thrive, have also caused plants and animals to go extinct. The report states that 40% of amphibians, 33% of ocean mammals, 33% of sharks, and 10% of insects could soon go extinct.\r\n\r\nIf humans are not more mindful, extinctions will continue to speed up. Therefore, it is helpful to know how people negatively impact the planet. Here are four ways that people are speeding up extinction.\r\n\r\n1. Fewer Places to Live\r\nThe top threat to species on land is habitat loss. About 75% of land on Earth has been changed to build cities and increase farmland.ÃŠ Since 1992, cities have grown by more than 100%. To feed more people, healthy habitats have been turned into farmland. 85% of wetlands and 32% of rainforest that were around in 1760 are gone. For example, rainforests are being replaced with cattle ranches. All that land development makes it hard for animals to find a good place to live.\r\n\r\n2. Overfishing the Oceans\r\nOverfishing is the greatest human danger that ocean creatures face. People love seafood- over 3 billion people rely on seafood for protein! 55% of the ocean\'s surface is fished, and about 33% of the ocean\'s fish are overfished. This means there is not a lot of these fish left behind after the fishing is done. Tuna is one of the most overfished species in the world and their numbers are shrinking in the wild. Another downside of overfishing is that other animals, like turtles and dolphins, also get trapped in fishing nets. These unwanted catches are called bycatch.\r\n\r\n3. Dirtying the environment\r\nHumans have not done enough to cut down on pollution. One of the biggest problems is our love of plastic. It\'s everywhere! Ocean plastic has increased by ten times since 1980. It has harmed 267 species of ocean animals. They mistake the plastic for food or get trapped in it. On land, tiny pieces of plastic end up in the soil or in drinking water. Other sources of pollution, like oil spills or dirty drinking water, is also a problem. Pollution can harm an animal\'s health and make a place unlivable.\r\n\r\n4. Paving the way for invaders\r\nHumans bring invasive species to new areas as they travel the world. Invasive species not only compete with native species for food but can wipe them out. Across 21 countries, the number of invasive species has grown by 70 % since 1970, the report finds.\r\n\r\nBut there\'s hope\r\nHumans can slow extinctions, the researchers note. Conservation efforts have lowered the risk that many plants and animals will go extinct. To save more species, people need to rethink their behavior including how they use land, grow food, and what they throw out.',
    'Extinctions_prompt.m4a',
    'extinctions.m4a',
    'Science News',
    'Write a scientific argument that answers the question below: \r\n\r\nHow have human activities caused many plant and animal species to go extinct?\r\n\r\nRemember, a good science argument (1) clearly states your claim, (2) gives detailed facts to support your claim, (3) uses science ideas to persuade the reader that your facts support your claim (4) has a conclusion that helps the reader understand why they should agree with your answer, and (5) follows the rules of writing.',
    'ACTIVE',
    271,
    '2023-10-18 03:02:00',
    'DacquesV',
    '2023-10-18 03:07:01',
    'DacquesV'
  ),
  (
    10,
    'Invasive Carp',
    'A Diet to Fuel an Invasive Carp',
    '84% of the freshwater in the United States is in the Great Lakes. People use the lakes for fishing, boating, wildlife viewing, and recreation. That business brings in $8.5 billion a year. Keeping the lakes enjoyable depends on a healthy ecosystem. But keeping Lake Michigan healthy and free of invading critters is a stressful job.  Two species of invasive fish, the bighead and silver carp, have been wreaking havoc on the Mississippi River. At first, scientists were not worried about the fish getting into the Great Lakes. They thought there was not enough for the fish to eat.  However, a new study found that if invasive carp reach Lake Michigan, they might be able to survive on a buffet of mussel poop.  Now, scientists are both worried and disgusted.\r\n\r\nNot Such a Great Place to Live\r\nScientists once thought that the Great Lakes was a food desert for the carp. A food desert is any place that an animal has a hard time finding food. Animals tend to look somewhere else for food. Sandra Cooke studies freshwater ecosystems, like the Great Lakes. Cooke says the carp prefer to eat phytoplankton, which is a type of algae. These tiny plants live in warm water. Because the Great Lakes get so cold, scientists thought there wasn t enough food to satisfy the carp s big appetite. A warm lakeshore might have enough algae for the fish to eat in the summer. In the winter though, the fish would starve. \r\n\r\nPeter Alsip, a scientist from the University of Michigan, says the carp are not picky eaters. When there are no good food options available, the fish will live off detritus. Detritus, or animal waste, includes fish and mussel poop and dead organisms. Detritus is like junk food for fish. Alsip and his team studied Lake Michigan, the fishes  appetite   including for junk food   and their energy needs to predict where the carp could live. Invasive zebra and quagga mussels cover much of the lake floor. Mussel poop could make the deeper, colder parts of the lake livable for the invasive fish. The carp could eat mussel poop to survive a Lake Michigan winter.\r\n      \r\nA Costly Mistake\r\nBighead and silver carp were brought to the United States in the 1970s to control the growth of algae, which thrived in polluted rivers. But during floods, the fish escaped into the wild. Soon, the fish found new homes in the Mississippi River. They have been traveling north ever since and are knocking on the Great Lakes  doorstep. If the carp can eat mussel poop and algae, 75% of Lake Michigan could be a comfortable place for the fish to live. The fish would likely use the lake as a highway, traveling from lake to lake in search of warmer waters and better food.\r\n\r\nThe last invasive species to sneak into the Great Lakes was the quagga mussel. It now covers the bottom of Lake Michigan. When the mussels first invaded, they changed the chemistry of the water. This changed killed many native whitefish and caused waterbirds to get sick. Businesses spend $100,000 a year to remove the mussels from water that comes out of the lake.  Once an invasive species travels someplace new, the damage is expensive and can t be undone.\r\n\r\nIf carp gain a finhold in Lake Michigan, their populations could take off.   We should be doing everything we can to keep bighead and silver carps out of the Great Lakes,  says Sandra Cooke.  Time and again, what we actually observe is worse than what we predicted in the first place,  she says.',
    'InvasiveCarp_prompt.m4a',
    'InvasiveCarp.m4a',
    'Science News',
    'Write a scientific argument that answers the question below:\r\nHow might an invasive species be successfully introduced to new environments?\r\nRemember, a good science argument (1) clearly states your claim, (2) gives detailed facts to support your claim, (3) uses science ideas to persuade the reader that your facts support your claim (4) has a conclusion that helps the reader understand why they should agree with your answer, and (5) follows the rules of writing.',
    'ACTIVE',
    264,
    '2023-10-18 03:20:02',
    'DacquesV',
    '2023-10-18 03:20:02',
    'DacquesV'
  ),
  (
    11,
    'Loving Plants',
    'CO2-loving Plants',
    'There are holidays for everything. We celebrate our love for our dogs, for donuts, and so much more during unofficial holidays. On the last Friday in April, the nation celebrates Arbor Day. Arbor Day is all about appreciating and celebrating trees. Volunteers across the country plant trees to celebrate nature and the environment. A new study may give the country more of a reason to celebrate trees. According to the study, during 2002 to 2014 plants slowed the rate of CO2 collecting in the air. \r\n      \r\nPlants Make Their Own Energy\r\n      From the tallest trees to the smallest flower, plants are amazing. Plants use the energy in sunlight to make their own food. Plants collect carbon dioxide gas (also called CO2) from the air and water from the soil and turn it into sugar. This chemical process is called photosynthesis.  When there is more CO2 in the air, plants can make more sugars through photosynthesis. The plants can grow quickly!\r\n      \r\n      The sugar that plants make can be stored for safekeeping until a later time. Plants may store their sugars in their roots as bulbs (like tulips) and tubers (like potatoes), or in their stems (like celery). This stored sugar becomes a food source that plants can use to grow, to flower, and to make seeds. Plant growth is slow. This process is the opposite of photosynthesis. It is called respiration, and it releases CO2 into the air. \r\n      \r\nSlowing the Rise of CO2\r\n      81% of the energy that the United States uses coms from fossil fuels. A fossil fuel is energy that comes from burning fossilized plants and animal.  Fossil fuels include coal, natural gas, and oil. These fuels release a lot of energy when burned. They also release CO2 into the air. CO2 is very good at trapping heat. This can fuel climate change. 75% of the carbon dioxide in the air is from fossil fuels. What can be done to lower the amount of carbon dioxide in the air?\r\n      \r\n      According to the study, from 2002 to 2014 the amount of CO2 people released in the air rose from 372 parts per million to 397 parts per million. During that time, people burned a lot of fossil fuels. Though CO2 increased, it wasn\'t as quick as scientists were expecting. After lots of study, they concluded that plants slowed the rate that CO2 collected in the air. Each year, land plants and the oceans remove about 45% of the CO2 emitted from human activities. The amount of CO2 they have absorbed has doubled over the last 50 years.\r\n      \r\nPlants Can\'t Do It All\r\n      Today, carbon dioxide enters the air more quickly than plants can absorb it. As CO2 collects, the climate warms. When plants are too warm, they are less effective at photosynthesis. Instead, plants respire. All that plant breathing releases CO2. \r\n      \r\n      In the early 2000s, the rate of rising CO2-concentrations outpaced the rate of global warming. This caused plants to absorb more CO2 during photosynthesis than they released during respiration. That imbalance slowed the buildup of atmospheric CO2. While the amount of CO2ÃŠincreased at a rate of 0.75 parts per million in 1959, it rose to a rate of 1.86 parts per million thirty years later. But between 2002 and 2014, the rate held at around 1.9 parts per million.\r\n      \r\n      Trevor Keenan wrote the study. \"If we keep emitting as much as we are, and what we emit keeps going up, then it won\'t matter very much what the plants do,\" he warns. While plants keep CO2 out of the air, planting more trees every Arbor Day is not the solution to slowing climate change.',
    'CO2LovingPlants_prompt.m4a',
    'CO2LovingPlants.m4a',
    'Nature Communications',
    'Write a scientific argument that answers the question below:\r\n\r\nHow are plants useful in the fight against climate change?   \r\n\r\nRemember, a good science argument (1) clearly states your claim, (2) gives detailed facts to support your claim, (3) uses science ideas to persuade the reader that your facts support your claim (4) has a conclusion that helps the reader understand why they should agree with your answer, and (5) follows the rules of writing.',
    'ACTIVE',
    313,
    '2023-10-18 03:33:06',
    'DacquesV',
    '2023-10-18 03:33:06',
    'DacquesV'
  ),
  (
    12,
    'Rooftop Gardens',
    'Fertilizer for Rooftop Gardens',
    'Living in a city can be exciting. Cities provide many chances for work and play. New York is one of the largest and densest cities in the United States. 8.8 million people live in 468 square miles of space! According to the United Nations, 55% of the entire worldÃ•s population lives in cities. Providing homes to so many people in a small area often means building upwards. The most famous cities in the world are known for their impressive skyscrapers. By 2050, over 68% of the world\'s population will live in cities. Scientists and urban planners are working together to find ways to make city living even better. They want to lower the amount of pollution in cities by increasing the amount of green space. One way to do this is to turn unused rooftop space into gardens.\r\n      \r\nLots of Energy to Keep Cool\r\nLiving in a city can be tough. Cities have few green spaces, such as parks or forests, compared to rural areas. Land must be cleared to make room for buildings and factories. Cities need to build hundreds of miles of roads and sidewalks so people can move around. Keeping a city running requires a lot of energy, which produces a lot of gases that linger in the air. These gases are sometimes called greenhouse gases. As a result, cities have a great deal of air pollution, or smog. Smog can make people ill. In fact, cities are responsible for 70% of the greenhouse gases that are released into the air. \r\n      \r\nSince cities have fewer shade trees, cities are warmer than nearby areas. This is called the heat island effect. A lot of energy used by cities is used to keep buildings cool. Bare city rooftops soak up heat from the sun, which warms the building. In response, air conditioners turn on to bring the temperature back down. Air conditioners use energy that often comes from coal-fired power plants. When coal burns, it releases CO2 into the air. CO2 is a greenhouse gas.\r\n      \r\nTurning Carbon into Fertilizer\r\nMany scientists are studying how well rooftop gardens work in lowering city pollution. Plants \"breathe in\" CO2 from the air during photosynthesis. This process allows plants to make their own energy and grow. Most rooftop garden plants are smaller and less healthy than plants in regular gardens. This may be because rooftop gardens get more solar radiation and wind. The water in the soil may evaporate faster. These conditions may limit how fast young, fragile plants can grow.\r\n      \r\nDr. Sarah Buckley works at Boston University. She came up with a genius way of helping rooftop plants out. She created a new device that connects to exhaust fans at the tops of buildings. It funnels any CO2 gas from the building onto a garden bed. The gas acts like a fertilizer to help the plants grow. Her team grew spinach and corn plants on a roof to test how well the device works. Some plants grew next to the building exhaust vents, which was rich in CO2. The control plants grew next to a regular fan. They did not get extra CO2 from the building. The plants that grew next to the building\'s vents grew 4 times larger than the control plants!\r\n      \r\nThe CO2 inside buildings can help rooftop plants grow larger. The new devices are part of a plan to make rooftop gardening easier. Buckley says rooftop gardens have many benefits, such as energy savings for the building, urban heat reduction, local food production, community building, and aesthetic and mental health benefits. Windy rooftops are still a challenge for rooftop gardens since wind stunts a plants growth.  However, her invention is just the first step in making gardens on every roof possible.',
    'RooftopGardens_prompt.m4a',
    'RooftopGardens.m4a',
    'Good News Network',
    'Write a scientific argument that answers the question below:\r\n\r\nHow might people use plants to fight air pollution in cities?\r\n\r\nRemember, a good science argument (1) clearly states your claim, (2) gives detailed facts to support your claim, (3) uses science ideas to persuade the reader that your facts support your claim (4) has a conclusion that helps the reader understand why they should agree with your answer, and (5) follows the rules of writing.',
    'ACTIVE',
    289,
    '2023-10-18 03:38:37',
    'DacquesV',
    '2023-10-18 03:38:37',
    'DacquesV'
  ),
  (
    13,
    'Windmills',
    'Fertilizer for Rooftop Gardens',
    'Americans have been dealing with their share of natural disasters, such as hurricanes and heat waves. Many are worried that these disasters are fueled by climate change. Afterall, climate change raises the odds that bad weather will happen. Climate change is a shift in the normal patterns of temperature and weather of an area. Scientists agree that human activities, like burning coal, cause climate to change more quickly today than in the past. Coal releases a lot of carbon dioxide into the air when it is burned.  CO2 in the air acts like a blanket: it traps heat and warms the planet. It is also a blanket that is hard to take off. It is easier to put CO2 into the air than it is to take it out. \r\n\r\nThe United States gets most of its electricity from coal. Scientists are looking for ways to get power from other energy sources. Renewable energies, like wind power, release almost zero CO2 into the air. They also never disappear.  Now, some engineers are trying to build a windmill that not only makes energy but also removes CO2 from the air. \r\n\r\nWindmills Pull in Dirty Air\r\nWindmills are also called wind turbines. They are tall structures with blades that reach into the sky. The blades are attached at an angle so that they spin when the wind blows. This spinning turns a set of gears, which then starts a generator. The generator turns the energy from the spinning blades into electricity! The faster a windmill spins, the more energy it makes. \r\n\r\nLuciano Castillo is an engineer. He says windmills also pull air down behind them when they spin. Castillo\'s team uses computers to test the carbon removing power of windmills. According to their data, windmills may pull down CO2 from the air. CO2 could be removed from the air if it can make it to the windmills. \r\n\r\nPros and Cons\r\nHaving carbon removing windmills in cities could be useful. Windmills may lower the amount of pollution in and around cities by pulling pollution out of the air. This would be helpful for cities since they seem to always be clouded in dirty air. The dirty air comes from cars and factories. Windmills could also lower the cost of electricity. Windmills could make the cost of taking CO2 out of the air cheaper too. \r\n\r\nSome people doubt that CastilloÃ•s idea will work. They say that the CO2 made by power plants is too high in the air. The windmills would not reach it to pull it down. Others worry that a windmill farm could damage the environment. According to a Harvard study, the number of windmills required to meet AmericaÃ•s energy needs would heat the country up by 0.43 degrees! That warming would cancel out the climate benefit of the windmills for at least 100 years. The study also says that the US is unlikely to use wind power as its only energy source. Perhaps the pros of using wind power outweighs the cons.\r\n\r\nNext Steps\r\nCastilloÃ•s windmills are not in use, yet. He hopes to scale up his study to test if windmills really can capture CO2. He would like his next study to take place in Chicago. Chicago is called The Windy City. \"The beauty is that around Chicago, you have one of the best wind resources in the region, so you can use the windmills to take some of the dirty air in the city and capture it,\" Castillo says.',
    'MulittaskingWindmills_prompt.m4a',
    'MulittaskingWindmills.m4a',
    'Science News',
    'Write a scientific argument that answers the question below:\r\n\r\nHow do people use technology to solve difficult problems while also protecting the environment?\r\n\r\nRemember, a good science argument (1) clearly states your claim, (2) gives detailed facts to support your claim, (3) uses science ideas to persuade the reader that your facts support your claim (4) has a conclusion that helps the reader understand why they should agree with your answer, and (5) follows the rules of writing.',
    'ACTIVE',
    117,
    '2023-10-18 03:42:14',
    'DacquesV',
    '2023-10-18 03:42:14',
    'DacquesV'
  );

CREATE TABLE `quiz_template` (
  `QT_ID` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `QT_TITLE` varchar(40) DEFAULT NULL,
  `QT_DESCRIPTION` varchar(200) DEFAULT NULL,
  `QT_PROMPT_1` varchar(40) DEFAULT NULL,
  `QT_PROMPT_2` varchar(40) DEFAULT NULL,
  `QT_PROMPT_3` varchar(40) DEFAULT NULL,
  `QT_GRADES` varchar(20) DEFAULT NULL,
  `QT_STATUS` varchar(10) DEFAULT NULL,
  `QT_CREATED_AT` datetime DEFAULT NULL,
  `QT_CREATED_BY` varchar(20) DEFAULT NULL,
  `QT_MODIFIED_ON` datetime DEFAULT NULL,
  `QT_MODIFIED_BY` varchar(20) DEFAULT NULL
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb3 COLLATE = utf8mb3_unicode_ci;

INSERT INTO
  `quiz_template` (
    `QT_ID`,
    `QT_TITLE`,
    `QT_DESCRIPTION`,
    `QT_PROMPT_1`,
    `QT_PROMPT_2`,
    `QT_PROMPT_3`,
    `QT_GRADES`,
    `QT_STATUS`,
    `QT_CREATED_AT`,
    `QT_CREATED_BY`,
    `QT_MODIFIED_ON`,
    `QT_MODIFIED_BY`
  )
VALUES
  (
    2,
    '4b_info',
    '4th grade quiz set #2',
    'edible wrappers',
    'plastic bottle village',
    'flies',
    '4',
    'ACTIVE',
    '2021-11-04 03:52:47',
    'DacquesV',
    '2021-11-12 05:15:15',
    'DacquesV'
  ),
  (
    3,
    '4c_info',
    '4th grade quiz set #3',
    'plastic bottle village',
    'flies',
    'elevated buses',
    '4',
    'ACTIVE',
    '2021-11-04 03:56:20',
    'DacquesV',
    '2021-11-04 03:56:20',
    'DacquesV'
  ),
  (
    7,
    '5b_info',
    '5th grade quiz set #2',
    'edible wrappers',
    'plastic bottle village',
    'flies',
    '5',
    'ACTIVE',
    '2021-11-04 03:52:47',
    'DacquesV',
    '2021-11-04 03:52:47',
    'DacquesV'
  ),
  (
    8,
    '5c_info',
    '5th grade quiz set #3',
    'plastic bottle village',
    'flies',
    'elevated buses',
    '5',
    'ACTIVE',
    '2021-11-04 03:56:20',
    'DacquesV',
    '2021-11-04 03:56:20',
    'DacquesV'
  ),
  (
    9,
    'form_a',
    'Whitehall 2024',
    'extinctions',
    'Invasive Carp',
    'Loving Plants',
    '4',
    'ACTIVE',
    '2023-12-01 12:50:15',
    'JosephP',
    '2023-12-01 12:50:15',
    'JosephP'
  ),
  (
    10,
    'form_b',
    'Whitehall 2024',
    'Invasive Carp',
    'extinctions',
    'Loving Plants',
    '4',
    'ACTIVE',
    '2023-12-01 12:50:56',
    'JosephP',
    '2023-12-01 12:50:56',
    'JosephP'
  );

CREATE TABLE `reports` (
  `R_ID` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `R_AID` int(11) NOT NULL,
  `R_DATE` datetime NOT NULL,
  `R_DATESTAMP` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `R_DATA` text NOT NULL
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb3 COLLATE = utf8mb3_general_ci;

CREATE ALGORITHM = UNDEFINED DEFINER = `waAdmin1` @`%` SQL SECURITY DEFINER VIEW `v_quiz_scores` AS
SELECT
  `config_users`.`USER_CODE` AS `USER_CODE`,
  `config_users`.`USER_LAST_NAME` AS `USER_LAST_NAME`,
  `config_users`.`USER_FIRST_NAME` AS `USER_FIRST_NAME`,
  `config_classes`.`CLASS_ID` AS `CLASS_ID`,
  `config_classes`.`CLASS_NAME` AS `CLASS_NAME`,
  `config_classes`.`CLASS_TEACHER_ID` AS `CLASS_TEACHER_ID`,
  `quiz`.`Q_ID` AS `Q_ID`,
  `quiz`.`Q_PROMPT_ID` AS `Q_PROMPT_ID`,
  `quiz`.`Q_PROMPT_TITLE` AS `Q_PROMPT_TITLE`,
  `quiz`.`Q_WORD_COUNT` AS `Q_WORD_COUNT`,
  `quiz`.`Q_SENTENCE_COUNT` AS `Q_SENTENCE_COUNT`,
  `quiz`.`Q_WORD_ERROR` AS `Q_WORD_ERROR`,
  `quiz`.`Q_SENTENCE_ERROR` AS `Q_SENTENCE_ERROR`,
  `quiz`.`Q_CIWS` AS `Q_CIWS`,
  `quiz`.`Q_WORD_ACCURACY` AS `Q_WORD_ACCURACY`,
  `quiz`.`Q_SENTENCE_ACCURACY` AS `Q_SENTENCE_ACCURACY`,
  `quiz`.`Q_WORD_COMPLEXITY` AS `Q_WORD_COMPLEXITY`,
  `quiz`.`Q_SENTENCE_COMPLEXITY` AS `Q_SENTENCE_COMPLEXITY`,
  `quiz`.`Q_TYPE_WORD_COUNT` AS `Q_TYPE_WORD_COUNT`,
  `quiz`.`Q_CHARACTER_COUNT` AS `Q_CHARACTER_COUNT`,
  `quiz`.`Q_TYPING_CORRECT` AS `Q_TYPING_CORRECT`,
  octet_length(`quiz`.`Q_TYPING_CHARS`) AS `Q_TYPING_CHARS`,
  `quiz`.`Q_PLANNING` AS `Q_PLANNING`,
  `quiz`.`Q_TIDE_T` AS `Q_TIDE_T`,
  `quiz`.`Q_TIDE_I` AS `Q_TIDE_I`,
  `quiz`.`Q_TIDE_D` AS `Q_TIDE_D`,
  `quiz`.`Q_TIDE_E` AS `Q_TIDE_E`,
  `quiz`.`Q_TIDE_C` AS `Q_TIDE_C`,
  `quiz`.`Q_VERSION` AS `Q_VERSION`,
  `quiz`.`Q_VER_QID` AS `Q_VER_QID`,
  `quiz`.`Q_TYPING` AS `Q_TYPING`
FROM
  (
    (
      (
        `config_users`
        join `config_pupils` on(
          `config_pupils`.`PUPIL_STUDENTID` = `config_users`.`USER_ID`
        )
      )
      join `config_classes` on(
        `config_pupils`.`PUPIL_CLASSID` = `config_classes`.`CLASS_ID`
      )
    )
    join `quiz` on(
      `quiz`.`Q_STUDENT_ID` = `config_users`.`USER_CODE`
    )
  )
WHERE
  `quiz`.`Q_GRADING_STATUS` in ('Completed', 'In Progress');

CREATE ALGORITHM = UNDEFINED DEFINER = `waAdmin1` @`%` SQL SECURITY DEFINER VIEW `v_students_in_class` AS
SELECT
  `config_users`.`USER_STATUS` AS `USER_STATUS`,
  `config_users`.`USER_CODE` AS `USER_CODE`,
  `config_users`.`USER_LAST_NAME` AS `USER_LAST_NAME`,
  `config_users`.`USER_FIRST_NAME` AS `USER_FIRST_NAME`,
  `config_classes`.`CLASS_ID` AS `CLASS_ID`,
  `config_classes`.`CLASS_NAME` AS `CLASS_NAME`,
  `config_classes`.`CLASS_TEACHER_ID` AS `CLASS_TEACHER_ID`
FROM
  (
    (
      `config_users`
      join `config_pupils` on(
        `config_pupils`.`PUPIL_STUDENTID` = `config_users`.`USER_ID`
      )
    )
    join `config_classes` on(
      `config_pupils`.`PUPIL_CLASSID` = `config_classes`.`CLASS_ID`
    )
  );

-- Create seed organization and admin user
INSERT INTO
  `config_schools` (
    SCHOOL_NAME,
    SCHOOL_SN,
    SCHOOL_CONTACT,
    SCHOOL_STATUS,
    SCHOOL_CREATED_AT,
    SCHOOL_CREATED_BY,
    SCHOOL_MODIFIED_ON,
    SCHOOL_MODIFIED_BY
  )
VALUES
  (
    'AdminOrg',
    'AdminOrg',
    'admin@example.org',
    'ACTIVE',
    '1970-01-01 00:00:00',
    'admin',
    '1970-01-01 00:00:00',
    'admin'
  );

INSERT INTO
  `config_users` (
    USER_CODE,
    USER_LEVEL,
    USER_STATUS,
    USER_ORGANIZATION,
    USER_FIRST_NAME,
    USER_LAST_NAME,
    USER_EMAIL,
    USER_PASSWORD,
    USER_TIMEOUT,
    USER_CREATED_AT,
    USER_CREATED_BY,
    USER_MODIFIED_ON,
    USER_MODIFIED_BY,
    USER_SALT
  )
VALUES
  (
    'admin',
    'ADMIN',
    'ACTIVE',
    'AdminOrg',
    'Admin',
    'User',
    'admin@example.org',
    -- Default password is 'changeme'
    'dd4c9dba7511437ac1eb4c0897232620774f609a66c70874b4c195cc09b3020d7fc43a73bf73057cc6d11d0b301e8ef5e5d3c2ff0bb273ba82fcd04d83f7350c',
    90,
    '1970-01-01 00:00:00',
    'admin',
    '1970-01-01 00:00:00',
    'admin',
    '9d93fb19cfddb97f2db2b2282c0009c5'
  );

COMMIT;