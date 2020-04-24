Feature:
    In order to manage the users in my organization,
    As an organization,
    I must be able to list, edit and delete users in my organization.

    Scenario: As anonymous, I cannot list the users from an organization
        When I go to "/organizations/1/users"
        Then I should be on "/organizations/login"
        And the response status code should be 200

    Scenario: As an organization, I can list the users from my organization
        Given I am authenticated as "DT75"
        And I am on "/organizations"
        When I follow "Afficher la liste de mes bénévoles inscrits"
        Then I should be on "/organizations/10/users/"
        And the response status code should be 200
        And I should see "john.doe@resop.com"
        And I should not see "jane.doe@resop.com"
        And I should not see "jill.doe@resop.com"
        And I should not see "chuck.norris@resop.com"
        And I should not see "freddy.mercury@resop.com"

    Scenario: As a parent organization, I can list the users from my children organizations
        Given I am authenticated as "DT75"
        And I am on "/organizations"
        When I follow "Modifier mes structures"
        Then I should be on "/organizations/children"
        And the response status code should be 200
        When I follow "Liste des bénévoles"
        Then I should be on "/organizations/30/users/"
        And the response status code should be 200
        And I should see "jane.doe@resop.com"
        And I should not see "john.doe@resop.com"
        And I should not see "chuck.norris@resop.com"

    Scenario: As an admin of an organization, I cannot list the users from another organization
        Given I am authenticated as "DT75"
        When I go to "/organizations/20/users"
        Then the response status code should be 403

    Scenario Outline: As an organization, I can update a user from my organization or children organizations
        Given I am authenticated as "<login>"
        When I go to "/organizations/30/users"
        And I follow "Modifier"
        Then I should be on "/organizations/30/users/2/edit"
        And the response status code should be 200
        And the "user_identificationNumber" field should contain "990002A"
        And the "user_emailAddress" field should contain "jane.doe@resop.com"
        And the "user_firstName" field should contain "Jane"
        And the "user_lastName" field should contain "DOE"
        When I fill in the following:
            | user[identificationNumber] | 999999A                 |
            | user[emailAddress]         | john.bon.jovi@resop.com |
            | user[firstName]            | John                    |
            | user[lastName]             | BON JOVI                |
        And I press "Valider"
        Then I should be on "/organizations/30/users/"
        And the response status code should be 200
        And I should see "Les informations ont été mises à jour avec succès."
        When I go to "/organizations/30/users/2/edit"
        Then I should be on "/organizations/30/users/2/edit"
        And the response status code should be 200
        And the "user_identificationNumber" field should contain "999999A"
        And the "user_emailAddress" field should contain "john.bon.jovi@resop.com"
        And the "user_firstName" field should contain "John"
        And the "user_lastName" field should contain "BON JOVI"
        Examples:
            | login    |
            | DT75     |
            | UL 01-02 |

    Scenario: As an admin of an organization, I cannot update a user from another organizations
        Given I am authenticated as "DT75"
        When I go to "/organizations/40/users/3/edit"
        Then the response status code should be 403
#
#    @javascript
#    Scenario Outline: As an admin of an organization, I can delete a user from my organization or children organizations
#        Given I am authenticated as "<login>"
#        When I go to "/organizations/3/users"
#        And I press "Supprimer"
#        Then I should see "Vous êtes sur le point de supprimer le bénévole : Jill DOE ( 990003A ) et toutes ses disponibilités."
#        When I press "Supprimer"
#        Then I should be on "/organizations/3/users"
#        And the response status code should be 200
#        And I should see "Le bénévole a été supprimé avec succès."
#        And I should not see "jill.doe@resop.com"
#        Examples:
#            | login              |
#            | john.doe@resop.com |
#            | jane.doe@resop.com |
#
#    Scenario: As an admin of an organization, I cannot directly delete a user from my organization
#        Given I am authenticated as "john.doe@resop.com"
#        When I go to "/organizations/3/users/3/delete"
#        Then the response status code should be 405
#
    Scenario: As an admin of an organization, I cannot delete a user from another organization
        Given I am authenticated as "DT75"
        When I go to "/organizations/40/users"
        Then the response status code should be 403
        When I go to "/organizations/40/users/3/delete"
        Then the response status code should be 403

    Scenario: As an admin of an organization, I cannot access a user with a mismatched url
        Given I am authenticated as "DT75"
        When I go to "/organizations/10/users/2/edit"
        Then the response status code should be 404
