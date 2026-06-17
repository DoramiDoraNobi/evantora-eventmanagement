class User {
  final int id;
  final String name;
  final String email;
  final List<OrganizationRole> organizations;

  User({
    required this.id,
    required this.name,
    required this.email,
    required this.organizations,
  });

  factory User.fromJson(Map<String, dynamic> json) {
    return User(
      id: json['id'],
      name: json['name'],
      email: json['email'],
      organizations: (json['organizations'] as List?)
              ?.map((e) => OrganizationRole.fromJson(e))
              .toList() ??
          [],
    );
  }

  bool get isOrganizer => organizations.isNotEmpty;
}

class OrganizationRole {
  final int id;
  final String name;
  final String role;

  OrganizationRole({
    required this.id,
    required this.name,
    required this.role,
  });

  factory OrganizationRole.fromJson(Map<String, dynamic> json) {
    return OrganizationRole(
      id: json['id'],
      name: json['name'],
      role: json['pivot']?['role'] ?? json['role'] ?? 'staff',
    );
  }
}
