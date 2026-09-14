<?php

namespace App\Tools\Lab;

use App\Tools\Lab\Models\IdpActivity;

/**
 * The IDP Tracker's starting content, pulled straight from the original IDP
 * spreadsheet. Shared by the seeder (first run) and the "Reset" action, so
 * both always agree on what "back to the original plan" means.
 *
 * `apply()` only ever touches area/objective/description/type/status/
 * target_date/notes on these 30 fixed-id rows — it never deletes a row, so
 * sources, attachments, and reviews (which reference an activity by this
 * same id) are never orphaned by a reset.
 */
final class IdpSeedData
{
    public const DEADLINE = '2026-12-07';

    /**
     * area, objective, description, type, status — id is the row's index.
     *
     * @var array<int, array{0: string, 1: string, 2: string, 3: string, 4: string}>
     */
    private const ACTIVITIES = [
        ['System Architecture & Solution Design', 'Design and document the architecture for a new mid-sized application.', 'Lead the architectural design of a real project module, producing diagrams, technology choices, and trade-off analyses.', '70', 'In Progress'],
        ['System Architecture & Solution Design', 'Learn architectural decision-making from senior architects.', 'Participate in architecture review boards and pair with a senior architect during design sessions.', '20', 'In Progress'],
        ['System Architecture & Solution Design', 'Understand core architectural patterns and principles.', 'Complete a course on software architecture patterns (microservices, event-driven, layered) and read "Software Architecture: The Hard Parts."', '10', 'To Kick-off'],
        ['Application Security', 'Identify and remediate security vulnerabilities in an existing codebase.', 'Perform a security audit on a live application, fix findings, and implement secure coding fixes.', '70', 'To Kick-off'],
        ['Application Security', 'Build security awareness through peer collaboration.', 'Join a security-focused community of practice and conduct threat-modeling sessions with the team.', '20', 'In Progress'],
        ['Application Security', 'Master OWASP Top 10 and secure development principles.', 'Complete an OWASP/secure-coding certification course.', '10', 'To Kick-off'],
        ['Performance Optimization', 'Improve measurable performance of a slow application feature.', 'Profile a real application, identify bottlenecks, and optimize queries, caching, and front-end load times.', '70', 'In Progress'],
        ['Performance Optimization', 'Exchange optimization techniques with experienced engineers.', 'Shadow a performance engineer and present optimization results in a tech-share session.', '20', 'In Progress'],
        ['Performance Optimization', 'Learn profiling tools and performance fundamentals.', 'Take a course on web performance (Core Web Vitals, database tuning).', '10', 'To Kick-off'],
        ['Technical Leadership & Mentoring', 'Lead a small team through a project delivery.', 'Act as tech lead on a feature, owning technical decisions and coordinating the team.', '70', 'To Kick-off'],
        ['Technical Leadership & Mentoring', 'Develop mentoring and coaching skills.', 'Mentor a junior developer and seek feedback from your own manager or a senior leader.', '20', 'To Kick-off'],
        ['Technical Leadership & Mentoring', 'Understand leadership and communication frameworks.', 'Complete a technical leadership or engineering management course.', '10', 'To Kick-off'],
        ['DevOps & Deployment Management', 'Build and manage a CI/CD pipeline.', 'Set up automated build, test, and deployment pipelines for a real project.', '70', 'To Kick-off'],
        ['DevOps & Deployment Management', 'Learn deployment practices from DevOps peers.', 'Pair with a DevOps engineer and participate in incident retrospectives.', '20', 'To Kick-off'],
        ['DevOps & Deployment Management', 'Understand CI/CD and infrastructure-as-code fundamentals.', 'Complete a Docker/Kubernetes or CI/CD certification course.', '10', 'To Kick-off'],
        ['Project Planning and Estimation', 'Plan and estimate a full project or sprint.', 'Own the estimation and breakdown of work for a real deliverable and track actuals against estimates.', '70', 'In Progress'],
        ['Project Planning and Estimation', 'Improve estimation accuracy through team input.', 'Facilitate planning poker sessions and review estimation outcomes with the team.', '20', 'In Progress'],
        ['Project Planning and Estimation', 'Learn estimation techniques and agile planning.', 'Take a course on agile project management or estimation methods.', '10', 'In Progress'],
        ['Testing and Quality Assurance', 'Increase test coverage and quality on a project.', 'Write unit, integration, and end-to-end tests for a real feature and set up automated testing.', '70', 'In Progress'],
        ['Testing and Quality Assurance', 'Learn testing strategies from QA peers.', "Collaborate with QA engineers on test plans and review each other's test cases.", '20', 'In Progress'],
        ['Testing and Quality Assurance', 'Understand testing methodologies and frameworks.', 'Complete a course on test automation (Jest, Cypress, Playwright).', '10', 'In Progress'],
        ['API Design and Integration', 'Design and build a production-ready API.', 'Create a RESTful or GraphQL API with proper versioning, documentation, and error handling for a real project.', '70', 'In Progress'],
        ['API Design and Integration', 'Refine API design through peer review.', 'Conduct API design reviews and collaborate with consuming teams on contracts.', '20', 'In Progress'],
        ['API Design and Integration', 'Learn API design best practices and standards.', 'Take a course on REST/GraphQL design and OpenAPI specification.', '10', 'In Progress'],
        ['Database Design and Administration', 'Design and optimize a database schema.', 'Model a database for a real application, write migrations, and tune indexes and queries.', '70', 'In Progress'],
        ['Database Design and Administration', 'Learn database management from experienced DBAs.', 'Pair with a DBA on schema reviews and discuss scaling strategies.', '20', 'In Progress'],
        ['Database Design and Administration', 'Understand relational and NoSQL fundamentals.', 'Complete a course on database design and SQL performance tuning.', '10', 'In Progress'],
        ['Cloud Technologies', 'Deploy and manage an application on a cloud platform.', 'Provision and configure cloud infrastructure (compute, storage, networking) for a live workload.', '70', 'In Progress'],
        ['Cloud Technologies', 'Build cloud expertise through community learning.', 'Join a cloud user group and pair with a cloud engineer on architecture decisions.', '20', 'In Progress'],
        ['Cloud Technologies', 'Earn foundational cloud knowledge.', 'Complete a cloud certification (AWS Solutions Architect Associate, Azure Fundamentals).', '10', 'In Progress'],
    ];

    /**
     * Pre-generated target dates, staggered back from the deadline. Keyed by
     * "area||type" — Formal(10) leads, Social(20) mid, Experiential(70)
     * closes each competency.
     *
     * @var array<string, string>
     */
    private const TARGETS = [
        'System Architecture & Solution Design||10' => '2026-08-04',
        'System Architecture & Solution Design||20' => '2026-08-25',
        'System Architecture & Solution Design||70' => '2026-09-15',
        'Application Security||10' => '2026-08-13',
        'Application Security||20' => '2026-09-03',
        'Application Security||70' => '2026-09-24',
        'Performance Optimization||10' => '2026-08-21',
        'Performance Optimization||20' => '2026-09-11',
        'Performance Optimization||70' => '2026-10-02',
        'Technical Leadership & Mentoring||10' => '2026-08-30',
        'Technical Leadership & Mentoring||20' => '2026-09-20',
        'Technical Leadership & Mentoring||70' => '2026-10-11',
        'DevOps & Deployment Management||10' => '2026-09-07',
        'DevOps & Deployment Management||20' => '2026-09-28',
        'DevOps & Deployment Management||70' => '2026-10-19',
        'Project Planning and Estimation||10' => '2026-09-16',
        'Project Planning and Estimation||20' => '2026-10-07',
        'Project Planning and Estimation||70' => '2026-10-28',
        'Testing and Quality Assurance||10' => '2026-09-24',
        'Testing and Quality Assurance||20' => '2026-10-15',
        'Testing and Quality Assurance||70' => '2026-11-05',
        'API Design and Integration||10' => '2026-10-03',
        'API Design and Integration||20' => '2026-10-24',
        'API Design and Integration||70' => '2026-11-14',
        'Database Design and Administration||10' => '2026-10-11',
        'Database Design and Administration||20' => '2026-11-01',
        'Database Design and Administration||70' => '2026-11-22',
        'Cloud Technologies||10' => '2026-10-20',
        'Cloud Technologies||20' => '2026-11-10',
        'Cloud Technologies||70' => '2026-12-01',
    ];

    /** (Re)writes every seeded activity to its original id, area/objective/description/type/status/target — leaves sources, attachments, and reviews untouched. */
    public static function apply(): void
    {
        foreach (self::ACTIVITIES as $id => $row) {
            [$area, $objective, $description, $type, $status] = $row;

            IdpActivity::updateOrCreate(['id' => $id], [
                'area' => $area,
                'objective' => $objective,
                'description' => $description,
                'type' => $type,
                'status' => $status,
                'target_date' => self::TARGETS[$area.'||'.$type] ?? null,
                'notes' => null,
            ]);
        }
    }
}
