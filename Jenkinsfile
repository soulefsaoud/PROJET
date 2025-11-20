pipeline {
    agent any

    environment {
        REPO_URL = 'https://github.com/soulefsaoud/PROJET.git'
        DOCKER_IMAGE = 'compose-enjoy:latest'
        PROJECT_NAME = 'compose-enjoy'
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
                echo '=== Construction de l\'image Docker ==='
                sh 'docker build -t ${DOCKER_IMAGE} .'
            }
        }

        stage('🚀 Start Services') {
            steps {
                echo '=== Démarrage des services Docker ==='
                sh '''
                    docker-compose up -d
                    sleep 10
                    docker-compose ps
                '''
            }
        }

        stage('🧪 Run Unit Tests') {
            steps {
                echo '=== Exécution des tests unitaires ==='
                sh '''
                    docker-compose exec -T app php bin/phpunit --coverage-text || true
                '''
            }
        }

        stage('✅ Run Functional Tests') {
            steps {
                echo '=== Exécution des tests fonctionnels ==='
                sh '''
                    docker-compose exec -T app php bin/phpunit tests/Controller/ -v
                '''
            }
        }

        stage('🔍 Code Quality - Lint Twig') {
            steps {
                echo '=== Vérification de la syntaxe Twig ==='
                sh '''
                    docker-compose exec -T app php bin/console lint:twig templates/ || true
                '''
            }
        }

        stage('🔍 Code Quality - Lint YAML') {
            steps {
                echo '=== Vérification de la syntaxe YAML ==='
                sh '''
                    docker-compose exec -T app php bin/console lint:yaml config/ || true
                '''
            }
        }

        stage('🗑️ Cleanup') {
            steps {
                echo '=== Arrêt et nettoyage des conteneurs ==='
                sh '''
                    docker-compose down
                '''
            }
        }

        stage('🚀 Deploy to Production') {
            when {
                branch 'main'
                expression { currentBuild.result == null || currentBuild.result == 'SUCCESS' }
            }
            steps {
                echo '=== ✅ Déploiement en production ==='
                sh '''
                    docker-compose up -d
                    echo "✅ Application Compose & Enjoy déployée avec succès !"
                    docker-compose ps
                '''
            }
        }
    }

    post {
        always {
            echo '=== Nettoyage final ==='
            sh 'docker-compose down -v || true'
        }
        success {
            echo '✅ Pipeline exécutée avec succès !'
            // Optionnel : envoyer une notification email
        }
        failure {
            echo '❌ Erreur dans la pipeline !'
            // Optionnel : envoyer une alerte
        }
    }
}
