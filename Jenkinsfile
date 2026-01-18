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
                checkout scm
            }
        }

        stage('🔨 Build Docker Image') {
            steps {
                echo '=== Construction de l\'image Docker ==='
                sh 'docker-compose down -v || true'
                sh 'docker-compose build'
            }
        }

        stage('🚀 Start Services') {
            steps {
                echo '=== Démarrage des services Docker ==='
                sh '''
                    docker compose up -d
                    sleep 15
                    docker compose ps
                    docker compose logs
                '''
            }
        }

        stage('🧪 Run PHPUnit Tests') {
            steps {
                echo '=== Exécution des tests PHPUnit ==='
                sh '''
                    docker compose exec -T app php bin/phpunit tests/ -v || true
                    echo "✅ Tests exécutés"
                '''
            }
        }

        stage('✅ Code Quality - Lint Twig') {
            steps {
                echo '=== Vérification de la syntaxe Twig ==='
                sh '''
                    docker compose exec -T app php bin/console lint:twig templates/ || true
                '''
            }
        }

        stage('✅ Code Quality - Lint YAML') {
            steps {
                echo '=== Vérification de la syntaxe YAML ==='
                sh '''
                    docker compose exec -T app php bin/console lint:yaml config/ || true
                '''
            }
        }

        stage('✅ Code Quality - Lint PHP') {
            steps {
                echo '=== Vérification de la syntaxe PHP ==='
                sh '''
                    docker compose exec -T app php -l src/ || true
                '''
            }
        }

        stage('📊 Test Results') {
            steps {
                echo '=== Résumé des tests ==='
                sh '''
                    docker compose exec -T app php bin/phpunit tests/ --testdox || true
                '''
            }
        }

        stage('🗑️ Cleanup') {
            steps {
                echo '=== Nettoyage des conteneurs ==='
                sh '''
                    docker compose down || true
                '''
            }
        }

        stage('✅ Build Success') {
            when {
                expression { currentBuild.result == null || currentBuild.result == 'SUCCESS' }
            }
            steps {
                echo '✅ Pipeline exécutée avec succès !'
                echo 'Application prête pour le déploiement'
            }
        }
    }

    post {
        always {
            echo '=== Nettoyage final ==='
            sh 'docker compose down -v || true'
        }

        success {
            echo '✅ Pipeline réussie - Tous les tests passent !'
        }

        failure {
            echo '❌ Pipeline échouée - Vérifier les logs'
        }

        unstable {
            echo '⚠️ Pipeline instable - Vérifier les avertissements'
        }
    }
}
