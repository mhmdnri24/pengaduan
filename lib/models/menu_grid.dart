class MenuGrid {
  final String id;
  final String menuTitle;
  final String menuUrl;
  final String menuTipe;
  final String menuColor;
  final String menuAccentColor;
  final String menuIcon;
  final String menuStatus;
  final String createdAt;
  final String updatedAt;
  final String statusFormatted;
  final String tipeFormatted;
  final String createdAtFormatted;
  final String iconDisplay;
  final String colorDisplay;
  final String accentColorDisplay;

  MenuGrid({
    required this.id,
    required this.menuTitle,
    required this.menuUrl,
    required this.menuTipe,
    required this.menuColor,
    required this.menuAccentColor,
    required this.menuIcon,
    required this.menuStatus,
    required this.createdAt,
    required this.updatedAt,
    required this.statusFormatted,
    required this.tipeFormatted,
    required this.createdAtFormatted,
    required this.iconDisplay,
    required this.colorDisplay,
    required this.accentColorDisplay,
  });

  factory MenuGrid.fromJson(Map<String, dynamic> json) {
    return MenuGrid(
      id: json['id']?.toString() ?? '',
      menuTitle: json['menu_title'] ?? '',
      menuUrl: json['menu_url'] ?? '',
      menuTipe: json['menu_tipe'] ?? '',
      menuColor: json['menu_color'] ?? '',
      menuAccentColor: json['menu_accentColor'] ?? '',
      menuIcon: json['menu_icon'] ?? '',
      menuStatus: json['menu_status']?.toString() ?? '',
      createdAt: json['created_at'] ?? '',
      updatedAt: json['updated_at'] ?? '',
      statusFormatted: json['status_formatted'] ?? '',
      tipeFormatted: json['tipe_formatted'] ?? '',
      createdAtFormatted: json['created_at_formatted'] ?? '',
      iconDisplay: json['icon_display'] ?? '',
      colorDisplay: json['color_display'] ?? '',
      accentColorDisplay: json['accent_color_display'] ?? '',
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'menu_title': menuTitle,
      'menu_url': menuUrl,
      'menu_tipe': menuTipe,
      'menu_color': menuColor,
      'menu_accentColor': menuAccentColor,
      'menu_icon': menuIcon,
      'menu_status': menuStatus,
      'created_at': createdAt,
      'updated_at': updatedAt,
      'status_formatted': statusFormatted,
      'tipe_formatted': tipeFormatted,
      'created_at_formatted': createdAtFormatted,
      'icon_display': iconDisplay,
      'color_display': colorDisplay,
      'accent_color_display': accentColorDisplay,
    };
  }
}

class MenuGridResponse {
  final List<MenuGrid> menuGrid;
  final int total;

  MenuGridResponse({
    required this.menuGrid,
    required this.total,
  });

  factory MenuGridResponse.fromJson(Map<String, dynamic> json) {
    final List<dynamic> menuGridList = json['menu_grid'] ?? [];
    final List<MenuGrid> menus =
        menuGridList.map((menuJson) => MenuGrid.fromJson(menuJson)).toList();

    return MenuGridResponse(
      menuGrid: menus,
      total: json['total'] ?? 0,
    );
  }
}
