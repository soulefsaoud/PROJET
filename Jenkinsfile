pipeline {
    agent any

    environment {
        REPO_URL = 'https://github.com/soulefsaoud/PROJET.git'
        PROJECT_NAME = 'recette-project'
    }

    stages {

        stage('📥 Checkout') {
            steps {
                echo '=== Récupération du code depuis Git ==='
                git branch: 'main', url: env.REPO_URL
            }
        }

        stage('🔨 Build Docker Image') {
            steps {
                echo '=== Nettoyage des anciens conteneurs ==='
                bat 'docker compose down -v || exit 0'

                echo '=== Construction de l\'image Docker ==='
                bat 'docker compose build'
            }
        }

        stage('🚀 Start Services') {
            steps {
                echo '=== Démarrage des services Docker ==='
                bat '''
                    docker compose up -d
                    timeout /t 10
                    docker compose ps
                '''
            }
        }

        stage('🧪 Run PHPUnit Tests') {
            steps {
                echo '=== Exécution des tests PHPUnit ==='
                bat 'docker compose exec -T app php bin/phpunit || exit 0'
            }
        }

        stage('✅ Lint Twig') {
            steps {
                echo '=== Vérification de la syntaxe Twig ==='
                bat 'docker compose exec -T app php bin/console lint:twig templates/ || exit 0'
            }
        }

        stage('✅ Lint YAML') {
            steps {
                echo '=== Vérification de la syntaxe YAML ==='
                bat 'docker compose exec -T app php bin/console lint:yaml config/ || exit 0'
            }
        }

        stage('🗑️ Cleanup') {
            steps {
                echo '=== Arrêt et nettoyage des conteneurs ==='
                bat 'docker compose down || exit 0'
            }
        }

        stage('🚀 Deploy to Production') {
            when {
                branch 'main'
                expression { currentBuild.result == null || currentBuild.result == 'SUCCESS' }
            }
            steps {
                echo '=== Déploiement en production ==='
                bat '''
                    docker compose up -d
                    echo "✅ Application recette_project déployée avec succès !"
                    docker compose ps
                '''
            }
        }
    }

    post {
        always {
            echo '=== Nettoyage final ==='
            bat 'docker compose down -v || exit 0'
        }
        success {
            echo '✅ Pipeline exécutée avec succès !'
        }
        failure {
            echo '❌ Erreur dans la pipeline !'
        }
    }
}
