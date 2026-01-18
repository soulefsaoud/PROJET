pipeline {
    agent any

    environment {
        // Définir les variables d'environnement
        DOCKER_COMPOSE_CMD = 'docker compose'
        PROJECT_NAME = 'mon-projet'
    }

    stages {

    stage('🐳 Install Docker Compose') {
        steps {
            sh '''
                apt-get update
                apt-get install -y docker-compose-plugin
                docker compose version
            '''
        }
    }


        stage('Vérification Environnement') {

            steps {
                script {
                    echo '=== Vérification de l\'environnement Docker ==='
                    sh 'docker --version'

                    // Détecter quelle commande docker compose utiliser
                    def composeV2Available = sh(
                        script: 'docker compose version 2>/dev/null',
                        returnStatus: true
                    ) == 0

                    if (composeV2Available) {
                        env.DOCKER_COMPOSE_CMD = 'docker compose'
                        echo 'Utilisation de Docker Compose v2'
                        sh 'docker compose version'
                    }
                }
            }
        }

        stage('Nettoyage') {
            steps {
                script {
                    echo '=== Nettoyage des conteneurs existants ==='
                    // Arrêter et supprimer les conteneurs avec gestion d'erreur
                    sh """
                        ${env.DOCKER_COMPOSE_CMD} down -v --remove-orphans || true
                        docker system prune -f || true
                    """
                }
            }
        }

        stage('Construction de l\'image Docker') {
            steps {
                script {
                    echo '=== Construction de l\'image Docker ==='
                    sh """
                        ${env.DOCKER_COMPOSE_CMD} build --no-cache --pull
                    """
                }
            }
        }

        stage('Démarrage des services') {
            steps {
                script {
                    echo '=== Démarrage des services ==='
                    sh """
                        ${env.DOCKER_COMPOSE_CMD} up -d
                    """
                }
            }
        }

        stage('Vérification des services') {
            steps {
                script {
                    echo '=== Vérification de l\'état des conteneurs ==='
                    sh """
                        ${env.DOCKER_COMPOSE_CMD} ps
                        docker ps
                    """

                    // Attendre que les services soient prêts
                    sleep(time: 10, unit: 'SECONDS')

                    // Vérifier les logs en cas de problème
                    sh """
                        ${env.DOCKER_COMPOSE_CMD} logs --tail=50
                    """
                }
            }
        }
    }

    post {
        success {
            echo '=== Pipeline exécuté avec succès ==='
        }

        failure {
            echo '=== Échec du pipeline ==='
            script {
                // Afficher les logs en cas d'échec
                sh """
                    echo "=== Logs des conteneurs ==="
                    ${env.DOCKER_COMPOSE_CMD} logs --tail=100 || true

                    echo "=== État des conteneurs ==="
                    docker ps -a || true
                """
            }
        }

        always {
            echo '=== Nettoyage final ==='
            script {
                // Optionnel : nettoyer les images non utilisées
                sh 'docker image prune -f || true'
            }
        }
    }
}
