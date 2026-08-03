<?php
require_once __DIR__ . '/../models/FinancialInsight.php';
require_once __DIR__ . '/../services/ForecastService.php';
require_once __DIR__ . '/../services/RecommendationService.php';

class NotificationService
{
    private $pdo;
    private $financialInsight;
    private $forecastService;
    private $recommendationService;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
        $this->financialInsight = new FinancialInsight($pdo);
        $this->forecastService = new ForecastService($pdo);
        $this->recommendationService = new RecommendationService($pdo);
    }

    public function getSmartNotifications($userId)
    {
        $predictions = $this->forecastService->getPredictions($userId);
        $notifications = [];

        foreach ($predictions as $prediction) {
            $notifications[] = [
                'type' => $prediction['type'],
                'message' => $prediction['message'],
                'time' => 'Maintenant'
            ];
        }

        $dailyRecommendation = $this->recommendationService->calculateDailyRecommendation($userId);
        if ($dailyRecommendation['recommended_savings'] > 0) {
            $notifications[] = [
                'type' => 'success',
                'message' => 'Aujourd\'hui, épargnez ' . number_format($dailyRecommendation['recommended_savings'], 2) . ' DT pour garder votre plan sur les rails.',
                'time' => 'Aujourd\'hui'
            ];
        }

        return array_slice($notifications, 0, 5);
    }
}
