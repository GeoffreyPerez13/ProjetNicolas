<?php
$pageTitle = 'Parcours';
require_once __DIR__ . '/../includes/header.php';
$events = getTimelineEvents();

$eventTypeLabels = [
    'exposition' => 'Exposition',
    'formation' => 'Formation',
    'prix' => 'Prix / Distinction',
    'residence' => 'Résidence',
    'publication' => 'Publication',
    'autre' => 'Autre'
];
?>

<div class="timeline-section">
    <h1>Parcours</h1>

    <?php if (!empty($events)): ?>
        <div class="timeline">
            <?php 
            $lastYear = '';
            foreach ($events as $event): 
                $showYear = ($event['year'] !== $lastYear);
                $lastYear = $event['year'];
            ?>
                <div class="timeline-item fade-in">
                    <?php if ($showYear): ?>
                        <div class="timeline-year"><?= e($event['year']) ?></div>
                    <?php endif; ?>
                    <span class="timeline-type"><?= e($eventTypeLabels[$event['event_type']] ?? $event['event_type']) ?></span>
                    <div class="timeline-title"><?= e($event['title']) ?></div>
                    <?php if (!empty($event['location'])): ?>
                        <div class="timeline-location"><?= e($event['location']) ?></div>
                    <?php endif; ?>
                    <?php if (!empty($event['description'])): ?>
                        <div class="timeline-description"><?= nl2br(e($event['description'])) ?></div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="empty-state">
            <p>Le parcours sera bientôt disponible.</p>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
