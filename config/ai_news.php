<?php

return [
    /*
    |--------------------------------------------------------------------------
    | AI News API Settings
    |--------------------------------------------------------------------------
    */
    'endpoint' => env('AI_NEWS_API_ENDPOINT', ''),
    'model' => env('AI_NEWS_MODEL', ''),
    'api_key' => env('AI_NEWS_API_KEY', ''),
    'temperature' => (float) env('AI_NEWS_TEMPERATURE', 0.7),
    'timeout' => 90,

    /*
    |--------------------------------------------------------------------------
    | Default Editorial Options
    |--------------------------------------------------------------------------
    */
    'default_language' => 'en',
    'default_author_email' => 'admin@gmail.com',

    /*
    |--------------------------------------------------------------------------
    | Automation Schedules & Lifecycle (Configured via .env)
    |--------------------------------------------------------------------------
    */
    // 1. Post Generation
    // Disabled by default: unreviewed mass auto-publishing is treated by Google
    // as scaled content abuse. Enable only with a human review step in place.
    'generate_enabled' => filter_var(env('AI_NEWS_GENERATE_ENABLED', false), FILTER_VALIDATE_BOOLEAN),
    'generate_schedule' => env('AI_NEWS_GENERATE_SCHEDULE', 'daily'),
    'generate_count' => (int) env('AI_NEWS_GENERATE_COUNT', 1),
    'generate_languages' => env('AI_NEWS_GENERATE_LANG', 'en'),

    // 2. Post Update by AI
    'update_enabled' => filter_var(env('AI_NEWS_UPDATE_ENABLED', false), FILTER_VALIDATE_BOOLEAN),
    'update_schedule' => env('AI_NEWS_UPDATE_SCHEDULE', '0 */6 * * *'),
    'update_age_hours' => (int) env('AI_NEWS_UPDATE_AGE_HOURS', 24),
    'update_limit' => (int) env('AI_NEWS_UPDATE_LIMIT', 2),

    // 3. Post Deletion by AI
    // Disabled by default: deleting your own published articles makes the site
    // look low-value and breaks inbound links and indexing.
    'delete_enabled' => filter_var(env('AI_NEWS_DELETE_ENABLED', false), FILTER_VALIDATE_BOOLEAN),
    'delete_schedule' => env('AI_NEWS_DELETE_SCHEDULE', '0 3 * * *'),
    'delete_after_days' => (int) env('AI_NEWS_DELETE_AFTER_DAYS', 30),
    'delete_min_views' => (int) env('AI_NEWS_DELETE_MIN_VIEWS', 50),
    'delete_keep_max' => (int) env('AI_NEWS_DELETE_KEEP_MAX', 500),
    'delete_cleanup_images' => filter_var(env('AI_NEWS_DELETE_CLEANUP_IMAGES', true), FILTER_VALIDATE_BOOLEAN),

    /*
    |--------------------------------------------------------------------------
    | Curated Worldwide News Topics Pool (for Automatic Random Generation)
    |--------------------------------------------------------------------------
    */
    'topics_pool' => [
        'technology' => [
            'Next-Generation Quantum Computing and Global Cybersecurity Implications',
            'Autonomous AI Agents Revolutionizing Global Enterprise Workflows',
            'Solid-State Battery Breakthroughs and the Future of Electric Vehicles',
            'Global Semiconductor Race: Next-Gen 2nm Fabrication Nodes and Supply Chains',
            'Commercial Space Stations and the Expanding Orbital Satellite Economy',
            'Brain-Computer Interfaces: Clinical Trials and Real-World Applications',
            'AI-Driven Nuclear Fusion Control: Accelerating Clean Energy Timelines',
            'Open-Source AI Models vs Proprietary Giants: The Global Geopolitical Battle',
        ],
        'business' => [
            'Global Central Banks and the Shift Toward Digital Currency Infrastructure',
            'Cross-Border Trade Corridors: How Supply Chains Are Realigning Worldwide',
            'Venture Capital Capitalization Trends in Renewable Tech and Robotics',
            'Artificial Intelligence Adoption Across Fortune 500 Financial Institutions',
            'Global Real Estate Dynamics in Megacities Amid Modern Remote Work Norms',
            'Commodities Outlook: Rare Earth Minerals, Lithium, and Energy Transitions',
            'Sovereign Wealth Funds Expanding Direct Investments in Critical Infrastructure',
        ],
        'health' => [
            'CRISPR Gene-Editing Therapeutics Gaining Regulatory Approvals Worldwide',
            'AI in Early Cancer Diagnostics: Clinical Accuracy and Global Hospital Deployments',
            'Global Vaccine Platforms Targeting Universal Coronaviruses and Future Pandemics',
            'Longevity Science and Cellular Senescence: Breakthroughs in Clinical Trials',
            'Mental Health Technology and Neurofeedback Therapy Innovations in 2026',
            'Global Healthcare Systems Tackling Antimicrobial Resistance With Phage Therapy',
        ],
        'climate' => [
            'Offshore Floating Wind Farms Reaching Commercial Scale Across Continents',
            'Direct Air Carbon Capture Facilities Expanding Operations Globally',
            'Smart Grids and Megawatt-Scale Battery Storage Redefining Power Reliability',
            'Global Reforestation Technologies Combining Drones and AI Satellite Mapping',
            'Ocean Ecosystem Conservation Treaties and Sustainable Marine Tech',
            'Geothermal Energy Deep-Drilling Projects Unlocking Baseload Clean Power',
        ],
        'sports' => [
            'Next-Generation Biometrics and AI Analytics Reshaping Global Football Scouting',
            'International Olympic Committee Expanding Digital Streaming and VR Fan Experiences',
            'Global Cricket Expansion: T20 Leagues Gaining Major Footprint in the Americas',
            'Hyper-Performance Athletic Recovery Technologies in Professional Tennis and Racing',
            'Formula 1 100% Sustainable Fuel Transition: Engineering and Competitive Outlook',
        ],
        'national' => [
            'Global Diplomatic Summits Addressing Clean Energy Security and Trade Resilience',
            'Smart City Infrastructure Projects Transforming Urban Transportation Globally',
            'International Space Alliances Expanding Lunar Exploration Roadmaps',
            'Global Maritime Shipping Corridors Embracing Automated Navigation and Green Fuel',
            'Major Public Infrastructure Investments Connecting Emerging Economic Hubs',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Journalistic Perspective Seeds (Ensures 100% Unique Angles)
    |--------------------------------------------------------------------------
    */
    'angles' => [
        'Comprehensive market impact analysis and future consumer implications',
        'Technological architectural breakdown, scalability hurdles, and timeline',
        'Global regulatory responses, international policy, and ethical safeguards',
        'Economic ripple effects, venture investment trends, and industry disruption',
        'Expert consensus, critical debates, and contrarian perspectives',
        'Real-world implementation case studies and practical field data',
    ],
];
