pipeline {
    agent {
        docker {
            image 'docker:26-cli'
            args '-v /var/run/docker.sock:/var/run/docker.sock --entrypoint=""'
        }
    }

    environment {
        REPO_URL = 'https://github.com/soulefsaoud/PROJET.git'
        PROJECT_NAME = 'recette-project'
    }

    stages {

        stage('🐳 Install Docker Compose') {
            steps {
                sh '''
                    apk add --no-cache docker-cli-compose
                    docker compose version
                '''
            }
        }

        stage('📥 Checkout') {
            steps {
                echo '=== Récupération du code ==='
                git branch: 'main', url: env.REPO_URL
            }
        }

        stage('🔨 Build Docker Image') {
            steps {
                echo '=== Nettoyage & build ==='
                sh '''
                    docker compose down -v || true
                    docker compose build
                '''
            }
        }

        stage('🚀 Start Services') {
            steps {
                sh '''
                    docker compose up -d
                    sleep 10
                    docker compose ps
                '''
            }
        }

        stage('🧪 PHPUnit Tests') {
            steps {
                sh 'docker compose exec -T app php bin/phpunit'
            }
        }

        stage('✅ Lint Twig') {
            steps {
                sh 'docker compose exec -T app php bin/console lint:twig templates/'
            }
        }

        stage('✅ Lint YAML') {
            steps {
                sh 'docker compose exec -T app php bin/console lint:yaml config/'
            }
        }

        stage('🚀 Deploy Production') {
            when {
                branch 'main'
            }
            steps {
                echo '=== Déploiement production ==='
                sh '''
                    docker compose up -d
                    docker compose ps
                '''
            }
        }
    }

    post {
        always {
            sh 'docker compose down -v || true'
        }
        success {
            echo '✅ Pipeline exécuté avec succès'
        }
        failure {
            echo '❌ Pipeline en échec'
        }
    }
}
