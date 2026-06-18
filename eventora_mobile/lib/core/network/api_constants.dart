class ApiConstants {
  // Use 10.0.2.2 for Android Emulator connecting to local host
  // Use 127.0.0.1 for iOS Simulator connecting to local host
  // Updated to use your local IP address for physical device testing
  static const String baseUrl = 'http://192.168.1.8:8000/api/v1';
  
  static const String login = '/auth/login';
  static const String register = '/auth/register';
  static const String me = '/auth/me';
  static const String logout = '/auth/logout';
  
  static const String events = '/events';
  
  // Buyer
  static const String buyerTickets = '/buyer/tickets';
  static const String buyerOrders = '/buyer/orders';
  
  // Organizer
  static const String organizerOrganizations = '/organizer/organizations';
  static String organizerDashboard(int orgId) => '/organizer/$orgId/dashboard';
  static String organizerEvents(int orgId) => '/organizer/$orgId/events';
  static String organizerCheckin(int orgId) => '/organizer/$orgId/checkin';
}
