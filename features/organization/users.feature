@users
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
        And I am on "/organizations/201"
        When I follow "Afficher la liste de mes bénévoles inscrits"
        Then I should be on "/organizations/201/users/"
        And the response status code should be 200
        And I should see "john.doe@resop.com"
        And I should not see "jane.doe@resop.com"
        And I should not see "jill.doe@resop.com"
        And I should not see "chuck.norris@resop.com"
        And I should not see "freddy.mercury@resop.com"

    Scenario: As a parent organization, I can list the users from my children organizations
        Given I am authenticated as "DT75"
        And I am on "/organizations/201"
        When I follow "Modifier mes structures"
        Then I should be on "/organizations/201/children/"
        And the response status code should be 200
        When I follow "Liste des bénévoles"
        Then I should be on "/organizations/201/users/?organizationId=203"
        And the response status code should be 200
        And I should see "jane.doe@resop.com"
        And I should not see "john.doe@resop.com"
        And I should not see "chuck.norris@resop.com"

    Scenario: As an admin of an organization, I cannot list the users from another organization
        Given I am authenticated as "DT75"
        When I go to "/organizations/202/users"
        Then the response status code should be 403

    @javascript
    Scenario: As an organization, I can display a user modal
        Given I am authenticated as "DT75"
        When I go to "/organizations/201/users/?organizationId=203"
        And I press "Afficher"
        And I wait for ".modal-show-user-inner" to be visible
        Then I should see "Modifier"
        And I follow "Modifier"
        Then I should be on "/organizations/201/users/102/edit"

    Scenario Outline: As an organization, I can update a user from my organization or children organizations
        Given I am authenticated as "<login>"
        When I go to "<edit_url>"
        And the response status code should be 200
        And the "user_identificationNumber" field should contain "990002A"
        And the "user_emailAddress" field should contain "jane.doe@resop.com"
        And the "user_firstName" field should contain "Jane"
        And the "user_lastName" field should contain "DOE"
        When I fill in the following:
            | user[identificationNumber]               | 999999A                 |
            | user[emailAddress]                       | john.bon.jovi@resop.com |
            | user[firstName]                          | John                    |
            | user[lastName]                           | BON JOVI                |
            | user[phoneNumber]                        | 0611111111              |
            | user[birthday][day]                      | 2                       |
            | user[birthday][month]                    | 2                       |
            | user[birthday][year]                     | 1980                    |
            | user[properties][organizationOccupation] | organizationOccupation  |
            | user[properties][vulnerable]             | 0                       |
            | user[properties][fullyEquipped]          | 0                       |
            | user[properties][drivingLicence]         | 0                       |
            | user[properties][occupation][choice]     | Pompier                 |
        And I press "Valider"
        Then I should be on "<list_url>"
        And the response status code should be 200
        And I should see "Les informations ont été mises à jour avec succès."
        When I go to "<edit_url>"
        Then I should be on "<edit_url>"
        And the response status code should be 200
        And the "user[identificationNumber]" field should contain "999999A"
        And the "user[emailAddress]" field should contain "john.bon.jovi@resop.com"
        And the "user[firstName]" field should contain "John"
        And the "user[lastName]" field should contain "BON JOVI"
        And the "user[phoneNumber]" field should contain "06 11 11 11 11"
        And the "user[birthday][day]" field should contain "2"
        And the "user[birthday][month]" field should contain "2"
        And the "user[birthday][year]" field should contain "1980"
        And the "user[properties][organizationOccupation]" field should contain "organizationOccupation"
        And the "user[properties][vulnerable]" field should contain "0"
        And the "user[properties][fullyEquipped]" field should contain "0"
        And the "user[properties][drivingLicence]" field should contain "0"
        And the "user[properties][occupation][choice]" field should contain "Pompier"
        Examples:
            | login    | list_url                                     | edit_url                          |
            | DT75     | /organizations/201/users/?organizationId=203 | /organizations/201/users/102/edit |
            | UL 01-02 | /organizations/203/users/                    | /organizations/203/users/102/edit |

    Scenario: As an admin of an organization, I cannot update a user from another organizations
        Given I am authenticated as "DT75"
        When I go to "/organizations/204/users/103/edit"
        Then the response status code should be 403

    @javascript
    Scenario Outline: As an organization, I can delete a user from my organization or children organizations
        Given I am authenticated as "<login>"
        When I go to "<edit_url>"
        And I follow "Supprimer"
        And I wait for "#delete-item-modal" to be visible
        Then I should see "Vous êtes sur le point de supprimer le bénévole suivant et toutes ses disponibilités : Jane DOE ( 990002A )."
        When I press "Supprimer"
        Then I should be on "<list_url>"
        And I should see "Le bénévole a été supprimé avec succès."
        And I should not see "jill.doe@resop.com"
        Examples:
            | login    | list_url                                     | edit_url                          |
            | DT75     | /organizations/201/users/?organizationId=203 | /organizations/201/users/102/edit |
            | UL 01-02 | /organizations/203/users/                    | /organizations/203/users/102/edit |

#    Scenario: As an admin of an organization, I cannot directly delete a user from my organization
#        Given I am authenticated as "john.doe@resop.com"
#        When I go to "/organizations/201/users/3/delete?organizationId=203"
#        Then the response status code should be 405

    Scenario: As an admin of an organization, I cannot delete a user from another organization
        Given I am authenticated as "DT75"
        When I go to "/organizations/204/users"
        Then the response status code should be 403
        When I go to "/organizations/204/users/103/delete"
        Then the response status code should be 403

    Scenario: As an admin of an organization, I cannot access an invalid user
        Given I am authenticated as "DT75"
        When I go to "/organizations/201/users/108/edit"
        Then the response status code should be 404
