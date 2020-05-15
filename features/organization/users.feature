@users
Feature:
    In order to manage the users in my organization,
    As an admin of an organization,
    I must be able to list, edit and delete users in my organization.

    Scenario: As an admin of an organization, I can list the users from my organization
        Given I am authenticated as "admin201@resop.com"
        And I am on "/organizations/201"
        When I follow "Afficher la liste de mes bénévoles inscrits"
        Then I should be on "/organizations/201/users/"
        And the response status code should be 200
        And I should see "admin201@resop.com"
        And I should not see "admin203@resop.com"
        And I should not see "jill.doe@resop.com"
        And I should not see "chuck.norris@resop.com"
        And I should not see "admin204@resop.com"

    Scenario: As an admin of a parent organization, I can list the users from my children organizations
        Given I am authenticated as "admin201@resop.com"
        And I am on "/organizations/201"
        When I follow "Modifier mes structures"
        Then I should be on "/organizations/201/children/"
        And the response status code should be 200
        When I follow "Liste des bénévoles"
        Then I should be on "/organizations/201/users/?organizationId=203"
        And the response status code should be 200
        And I should see "admin203@resop.com"
        And I should see "jill.doe@resop.com"
        And I should not see "admin201@resop.com"
        And I should not see "chuck.norris@resop.com"
        And I should not see "admin204@resop.com"

    Scenario: As an admin of an organization, I cannot list the users from another organization
        Given I am authenticated as "admin201@resop.com"
        When I go to "/organizations/202/users"
        Then the response status code should be 403

#    TODO Fix this test
#    @javascript
#    Scenario: As an admin of an organization, I can display a user modal
#        Given I am authenticated as "admin201@resop.com"
#        When I go to "/organizations/201/users/?organizationId=203"
#        And I follow "Afficher"
#        And I wait for ".ajax-modal-content" to be visible
#        Then I should see "Modifier"
#        And I follow "Modifier"
#        Then I should be on "/organizations/201/users/102/edit"

    Scenario Outline: As an admin of an organization, I can update a user from my organization or children organizations
        Given I am authenticated as "<login>"
        When I go to "<list_url>"
        And I follow "Modifier"
        Then I should be on "<edit_url>"
        And the response status code should be 200
        And the "user_identificationNumber" field should contain "990002A"
        And the "user_emailAddress" field should contain "admin203@resop.com"
        And the "user_firstName" field should contain "Jane"
        And the "user_lastName" field should contain "DOE"
        When I fill in the following:
            | user[identificationNumber] | 999999A                 |
            | user[emailAddress]         | john.bon.jovi@resop.com |
            | user[firstName]            | John                    |
            | user[lastName]             | BON JOVI                |
        And I press "Valider"
        Then I should be on "<list_url>"
        And the response status code should be 200
        And I should see "Les informations ont été mises à jour avec succès."
        When I go to "<edit_url>"
        Then I should be on "<edit_url>"
        And the response status code should be 200
        And the "user_identificationNumber" field should contain "999999A"
        And the "user_emailAddress" field should contain "john.bon.jovi@resop.com"
        And the "user_firstName" field should contain "John"
        And the "user_lastName" field should contain "BON JOVI"
        Examples:
            | login    | list_url                                     | edit_url                          |
            | DT75     | /organizations/201/users/?organizationId=203 | /organizations/201/users/102/edit |
            | UL 01-02 | /organizations/203/users/                    | /organizations/203/users/102/edit |

    Scenario Outline: As an admin of an organization, I cannot update a user from another organizations
        Given I am authenticated as "admin201@resop.com"
        When I go to "<url>"
        Then the response status code should be 403
        Examples:
            | url                               |
            | /organizations/201/users/103/edit |
            | /organizations/204/users/103/edit |

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
#        Given I am authenticated as "admin201@resop.com"
#        When I go to "/organizations/201/users/3/delete?organizationId=203"
#        Then the response status code should be 405

    Scenario: As an admin of an organization, I cannot delete a user from another organization
        Given I am authenticated as "admin201@resop.com"
        When I go to "/organizations/204/users"
        Then the response status code should be 403
        When I go to "/organizations/204/users/103/delete"
        Then the response status code should be 403

    Scenario: As an admin of an organization, I cannot access an invalid user
        Given I am authenticated as "admin201@resop.com"
        When I go to "/organizations/201/users/108/edit"
        Then the response status code should be 404

    Scenario: As an admin of an organization, I cannot access a user with a mismatched url
        Given I am authenticated as "admin201@resop.com"
        When I go to "/organizations/201/users/2/edit"
        Then the response status code should be 404

    Scenario Outline: As an admin of an organization, I can promote a user as admin of an organization and this user has admin privilege
        Given I am authenticated as "admin204@resop.com"
        When I go to "<url>"
        And I follow "Promouvoir administrateur de UL DE BRIE ET CHANTEREINE"
        Then I should be on "<url>"
        And the response status code should be 200
        And I should see "L'utilisateur \"<name>\" a été promu administrateur de UL DE BRIE ET CHANTEREINE avec succès."
        And I should see "Révoquer la fonction d'administrateur de UL DE BRIE ET CHANTEREINE"
        Given I am authenticated as "<email>"
        When I go to "/organizations/204"
        Then the response status code should be 200
        Examples:
            | url                               | name         | email                  |
            | /organizations/204/users/105/edit | Chuck NORRIS | chuck.norris@resop.com |
            | /organizations/204/users/101/edit | John DOE     | admin201@resop.com     |

    Scenario: As an admin of an organization, I can revoke user's admin privilege of an organization and this user doesn't have admin privilege anymore
        Given I am authenticated as "admin202@resop.com"
        When I go to "/organizations/204/users/4/edit"
        And I follow "Révoquer la fonction d'administrateur de UL DE BRIE ET CHANTEREINE"
        Then I should be on "/organizations/204/users/4/edit"
        And the response status code should be 200
        And I should see "Le privilège d'administrateur pour la structure UL-DE-BRIE-ET-CHANTEREINE de \"Freddy MERCURY\" a été révoquée avec succès."
        And I should see "Promouvoir administrateur de UL DE BRIE ET CHANTEREINE"
        Given I am authenticated as "admin204@resop.com"
        When I go to "/organizations/204"
        Then the response status code should be 403

    Scenario: As an admin of an organization, I cannot promote admin a user who is already admin
        Given I am authenticated as "admin202@resop.com"
        When I go to "/organizations/204/users/4/promote-admin"
        Then the response status code should be 400

    Scenario: As an admin of an organization, I cannot revoke a user who is not an admin
        Given I am authenticated as "admin202@resop.com"
        When I go to "/organizations/204/users/5/revoke-admin"
        Then the response status code should be 400
