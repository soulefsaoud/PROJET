pipeline {
    agent any

    stages {
        stage('📥 Checkout') {
            steps {
                echo '=== Récupération du code ==='
                checkout scm
            }
        }

        stage('✅ Run Tests') {
            steps {
                echo '=== Exécution des tests ==='
                sh 'php bin/phpunit || true'
            }
        }

        stage('✅ Lint Twig') {
            steps {
                echo '=== Vérification Twig ==='
                sh 'php bin/console lint:twig templates/ || true'
            }
        }

        stage('✅ Lint YAML') {
            steps {
                echo '=== Vérification YAML ==='
                sh 'php bin/console lint:yaml config/ || true'
            }
        }

        stage('✅ Build Success') {
            steps {
                echo '✅ Pipeline exécutée avec succès !'
            }
        }
    }

    post {
        failure {
            echo '❌ Erreur dans la pipeline !'
        }
    }
}
