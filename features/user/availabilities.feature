Feature:
    In order to fill in my availabilities
    As a user
    I must be able to create my account

    Scenario: As authenticated user, I can update my availabilities
        Given I am authenticated as "john.doe@resop.com"
        And I am on "/"
        When I follow "Mes disponibilités pour la semaine prochaine"
        And I check "tuesday" column
        And I press "Enregistrer mes disponibilités"
        Then I should be on "/"
        And I should see "Vos disponibilités ont été mises à jour avec succès."
        Then I follow "Mes disponibilités pour la semaine prochaine"
        And column "tuesday" should be checked

    Scenario: As authenticated user, I can remove my availabilities
        Given I am authenticated as "john.doe@resop.com"
        And I am on "/"
        When I follow "Mes disponibilités pour la semaine prochaine"
        And I uncheck "tuesday" column
        And I press "Enregistrer mes disponibilités"
        Then I should be on "/"
        And I should see "Vos disponibilités ont été mises à jour avec succès."
        Then I follow "Mes disponibilités pour la semaine prochaine"
        And column "tuesday" should be unchecked
