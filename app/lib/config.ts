/*
 * Copyright (C) 2026 Sami Saubion
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

export const PERSISTANT_COOKIES: string[] = ["cookie-banner-dismissed"];

/* Help Center config */

type Item = { q: string; a: string };
type Detail = { title: string; description: string; format: string };
type Subsection = { id: string; title: string; items: Item[]; details?: Detail[] };
type Section = { id: string; title: string; subsections: Subsection[] };

export const helpContent: Section[] = [
    {
        id: "getting-started",
        title: "Premiers pas",
        subsections: [
            {
                id: "gs-register",
                title: "Création du compte",
                items: [
                    {
                        q: "Comment créer mon compte Goralys ?",
                        a:
                            'Allez sur la page d\'accueil et cliquez sur "Se connecter". Cliquez ensuite sur le lien "page de connexion"' +
                            " dans le texte sur la partie gauche. Rentrez votre identifiant fourni par l'établissement, votre nom et mot" +
                            " de passe.\nNote: le nom que vous entrez à ce moment apparaîtra sur le document officiel du Grand oral." +
                            " Faites donc attention aux fautes, si toutefois, vous avez fais une erreur, contacter un administrateur qui" +
                            " réinitialisera votre compte. Vous pourrez ensuite le recréer avec le même identifiant.",
                    },
                ],
            },
            {
                id: "gs-connection",
                title: "Connexion",
                items: [
                    {
                        q: "Comment me connecter à Goralys ?",
                        a:
                            'Allez sur la page d\'accueil et cliquez sur "Se connecter". Entrez votre identifiant et mot de passe. Si vous ' +
                            "avez oublié votre mot de passe, contactez un administrateur de votre établissement.",
                    },
                ],
            },
            {
                id: "gs-subjects",
                title: "Questions",
                items: [
                    {
                        q: "Où trouver mes questions de Grand oral ?",
                        a:
                            'Une fois connecté, vous verrez toutes les questions disponibles via le menu "Questions" ("Mes Questions" pour les ' +
                            'élèves) ou bien "Mes Élèves" pour les professeurs.',
                    },
                ],
            },
        ],
    },
    {
        id: "students",
        title: "Guide des élèves",
        subsections: [
            {
                id: "st-consultation",
                title: "Consultation des questions",
                items: [
                    {
                        q: "Comment consulter mes questions ?",
                        a: 'Accédez à la liste complète de vos questions via le menu "Mes Questions". Vous pouvez ensuite les compléter.',
                    },
                    {
                        q: "Comment savoir si ma question est validée ?",
                        a:
                            "L'état de votre question est indiqué par un code couleur :\n— Soulignée en jaune : en attente de validation par" +
                            " votre professeur.\n— Verte : validée.\n— Rouge : rejetée, une modification est obligatoire.",
                    },
                    {
                        q: "Pourquoi ma question a été rejetée ?",
                        a:
                            "Votre professeur a laissé un commentaire expliquant le motif du rejet. Cliquez sur le menu déroulant sous votre" +
                            " question pour le consulter, puis modifiez votre question en conséquence.",
                    },
                ],
            },
            {
                id: "st-send",
                title: "Envoi d'une question",
                items: [
                    {
                        q: "Comment joindre un brouillon à ma question ?",
                        a:
                            "Au moment de l'envoi de votre question, un champ (pop-up) vous permet de joindre un fichier de brouillon." +
                            " Remplissez-le avant de valider l'envoi.",
                    },
                ],
            },
        ],
    },
    {
        id: "teachers",
        title: "Guide des professeurs",
        subsections: [
            {
                id: "te-gestion",
                title: "Gestion des questions",
                items: [
                    {
                        q: "Comment consulter les questions de mes élèves ?",
                        a: 'Accédez à "Mes élèves" pour voir toutes les questions de vos élèves.',
                    },
                    {
                        q: "Comment trier les questions de mes élèves ?",
                        a:
                            "Vous pouvez utiliser la barre de recherche pour trier les questions de vos élèves. Vous pouvez les trier par élève" +
                            ' (nom de famille) ou bien par matière. Vous pouvez utiliser des abréviations, par exemple: "nsi" pour Numérique' +
                            " et Sciences Informatiques, hggsp, llce, etc. \nNote: ces abréviations sont insensibles aux majuscules: nsi = NSI",
                    },
                ],
            },
        ],
    },
    {
        id: "admins",
        title: "Guide des administrateurs",
        subsections: [
            {
                id: "ad-import-format",
                title: "Format d'import",
                items: [
                    {
                        q: "Quel format pour importer les questions ?",
                        a: "Vous devez préparer un fichier groupes.csv et un fichier CSV par classe. Consultez les exemples de format ci-dessous.",
                    },
                ],
                details: [
                    {
                        title: "groupes.csv",
                        description:
                            "Liste des classes avec leur(s) professeur(s) responsable(s), une ligne par groupe. Si plusieurs" +
                            " professeurs sont assignés à un même groupe, séparez leur nom par un '|'. Enfin, les noms composés doivent" +
                            " être séparés par un espace.",
                        format: "TMATHS1, DUPONT LAMBERT Jean|PETIT Thomas\nTSVT1, DURANT Claire",
                    },
                    {
                        title: "TMATHS1_Mathématiques.csv",
                        description: "Fichier de la classe : première ligne = titre de section, puis un élève par ligne.",
                        format: "Élève\nDUBOIS Alice\nDURANT Sophie",
                    },
                    {
                        title: "TSVT1_Sciences et Vie de la Terre.csv",
                        description: "Même structure pour chaque classe définie dans groupes.csv.",
                        format: "Élève\nMARTIN Olivier\nBERNARD Luc",
                    },
                ],
            },
            {
                id: "ad-gestion",
                title: "Gestion des questions",
                items: [
                    {
                        q: "Comment importer les questions ?",
                        a:
                            'Allez dans "Questions" et cliquez sur "Importer les questions". Téléchargez vos fichiers CSV. Un fichier ' +
                            '"utilisateurs.txt" (identifiants/mots de passe) sera généré automatiquement. Ce dernier contient tous les' +
                            " identifiants des élèves et professeurs de l'établissement. Vous pouvez donc l'imprimer afin de distribuer" +
                            " ces identifiants aux concernés.",
                    },
                    {
                        q: "Comment exporter toutes les questions ?",
                        a: 'Cliquez sur "Exporter les sujets en PDF". Un fichier ZIP contenant toutes les fiches du Grand oral sera téléchargé.',
                    },
                    {
                        q: "Puis-je supprimer toutes les questions ?",
                        a: 'Oui, via "Supprimer les sujets". Une confirmation est demandée. Attention : cela supprime aussi tous les utilisateurs sauf les administrateurs.',
                    },
                ],
            },
            {
                id: "ad-users",
                title: "Gestion des utilisateurs",
                items: [
                    {
                        q: "Comment réinitialiser le mot de passe d'un utilisateur ?",
                        a:
                            'Allez dans "Utilisateurs" et trouvez l\'utilisateur concerné. Cliquez sur "Réinitialiser le mot de passe". ' +
                            "Son identifiant de connexion reste le même.",
                    },
                    {
                        q: "Comment supprimer un utilisateur ?",
                        a:
                            'Allez dans "Utilisateurs", trouvez l\'utilisateur et cliquez sur "Supprimer".\nAttention : si c\'est un élève,' +
                            " ses questions seront également supprimées. Si c'est un professeur alors toutes les matières qui lui sont" +
                            " associées seront également supprimées.",
                    },
                    {
                        q: "Comment remplacer un professeur ?",
                        a:
                            'Allez dans "Utilisateurs", trouvez le professeur à remplacer et cliquez sur "Remplacer le professeur". Entrez' +
                            " le nom et prénom du nouveau professeur. Une notification avec son identifiant apparaîtra et les questions des" +
                            " élèves du (des) groupes seront automatiquement transférées.",
                    },
                    {
                        q: "Comment consulter l'identifiant d'un utilisateur ?",
                        a:
                            'Dans "Utilisateurs", cliquez sur le nom de l\'utilisateur, une boîte de saisie de mot de passe apparaîtra,' +
                            " entrez-y votre mot de passe puis confirmez. Vous devriez voir une notification avec l'identifiant",
                    },
                    {
                        q: "Comment créer un nouvel administrateur ?",
                        a:
                            'Allez dans "Accès" pour créer un compte administrateur. Renseignez son nom et prénom. Une notification avec son' +
                            " identifiant de connexion apparaîtra, il pourra ensuite créer son compte.\nAttention : les administrateurs ont" +
                            " accès à toutes les fonctionnalités de gestion de l'établissement.",
                    },
                    {
                        q: "Comment supprimer un administrateur ?",
                        a: 'Dans "Accès", trouver l\'administrateur et cliquez sur "Révoquer l\'accès".',
                    },
                ],
            },
        ],
    },
    {
        id: "troubleshooting",
        title: "Dépannage",
        subsections: [
            {
                id: "tr-general",
                title: "Problèmes courants",
                items: [
                    {
                        q: "La page ne charge pas correctement",
                        a:
                            "Rafraîchissez la page (Ctrl+R). Vérifiez votre connexion Internet. Si le problème persiste, videz le cache ou" +
                            " essayez un autre navigateur.",
                    },
                    {
                        q: "L'import de questions échoue",
                        a:
                            'Vérifiez que vos fichiers CSV respectent bien le format décrit dans la section "Format d\'import". Si vous avez' +
                            " accès au serveur, veuillez consulter les logs (public_html/backend/Logs) pour plus d'informations.",
                    },
                    {
                        q: "Je ne vois pas les questions après l'import",
                        a: "Rafraîchissez la page. Si le problème persiste, essayez de vous déconnecter puis de vous re-connecter.",
                    },
                ],
            },
        ],
    },
];
