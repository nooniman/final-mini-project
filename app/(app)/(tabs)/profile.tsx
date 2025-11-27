/**
 * Profile Screen
 * User profile and quick settings
 */

import React from 'react';
import {
  View,
  StyleSheet,
  ScrollView,
  TouchableOpacity,
  Alert,
} from 'react-native';
import { router } from 'expo-router';
import { useSafeAreaInsets } from 'react-native-safe-area-context';
import { Ionicons } from '@expo/vector-icons';
import { useTheme } from '../../../src/context/ThemeContext';
import { useAuthStore } from '../../../src/store/useAuthStore';
import { Layout } from '../../../src/constants/Layout';
import {
  ThemedView,
  ThemedText,
  Card,
  Avatar,
  Badge,
} from '../../../src/components/ui';

const menuSections = [
  {
    title: 'Account',
    items: [
      { icon: 'person-outline', label: 'Edit Profile', route: '/(app)/settings' },
      { icon: 'lock-closed-outline', label: 'Change Password', route: '/(app)/settings' },
      { icon: 'notifications-outline', label: 'Notifications', route: '/(app)/settings' },
    ],
  },
  {
    title: 'Academic',
    items: [
      { icon: 'document-text-outline', label: 'Academic Records', route: '/(app)/(tabs)/grades' },
      { icon: 'calendar-outline', label: 'Class Schedule', route: '/(app)/(tabs)/subjects' },
      { icon: 'stats-chart-outline', label: 'Performance Report', route: '/(app)/(tabs)/grades' },
    ],
  },
  {
    title: 'Support',
    items: [
      { icon: 'help-circle-outline', label: 'Help & FAQ', route: null },
      { icon: 'chatbubble-outline', label: 'Contact Support', route: null },
      { icon: 'information-circle-outline', label: 'About', route: null },
    ],
  },
];

export default function ProfileScreen() {
  const { colors } = useTheme();
  const insets = useSafeAreaInsets();
  const { user, logout } = useAuthStore();

  const handleLogout = () => {
    Alert.alert(
      'Logout',
      'Are you sure you want to logout?',
      [
        { text: 'Cancel', style: 'cancel' },
        {
          text: 'Logout',
          style: 'destructive',
          onPress: async () => {
            await logout();
            router.replace('/sign-in');
          },
        },
      ]
    );
  };

  const handleMenuPress = (route: string | null) => {
    if (route) {
      router.push(route as any);
    } else {
      Alert.alert('Coming Soon', 'This feature will be available soon.');
    }
  };

  return (
    <ThemedView style={styles.container}>
      <ScrollView
        style={styles.scrollView}
        contentContainerStyle={[
          styles.scrollContent,
          { paddingTop: insets.top + Layout.spacing.md },
        ]}
        showsVerticalScrollIndicator={false}
      >
        {/* Profile Header */}
        <View style={styles.header}>
          <Avatar name={user?.fullName || 'Student'} size="xl" />
          <ThemedText variant="title2" style={styles.name}>
            {user?.fullName || 'Student Name'}
          </ThemedText>
          <ThemedText variant="subheadline" color="secondary">
            {user?.studentId || 'N/A'}
          </ThemedText>
          <Badge label={(user?.status || 'ACTIVE').toUpperCase()} variant="success" />
        </View>

        {/* Quick Info Card */}
        <Card variant="elevated" style={styles.infoCard}>
          <View style={styles.infoRow}>
            <View style={styles.infoItem}>
              <Ionicons
                name="school-outline"
                size={20}
                color={colors.primary}
              />
              <View style={styles.infoText}>
                <ThemedText variant="caption1" color="secondary">
                  Program
                </ThemedText>
                <ThemedText variant="subheadline" numberOfLines={1}>
                  {user?.programCode || user?.program || 'N/A'}
                </ThemedText>
              </View>
            </View>
            <View style={[styles.infoDivider, { backgroundColor: colors.divider }]} />
            <View style={styles.infoItem}>
              <Ionicons
                name="layers-outline"
                size={20}
                color={colors.primary}
              />
              <View style={styles.infoText}>
                <ThemedText variant="caption1" color="secondary">
                  Year & Section
                </ThemedText>
                <ThemedText variant="subheadline">
                  {user?.yearLevel || '-'}-{user?.section || '-'}
                </ThemedText>
              </View>
            </View>
          </View>

          <View style={[styles.divider, { backgroundColor: colors.divider }]} />

          <View style={styles.collegeInfo}>
            <Ionicons
              name="business-outline"
              size={18}
              color={colors.textTertiary}
            />
            <ThemedText variant="footnote" color="tertiary" style={styles.collegeName}>
              {user?.college || 'Western Mindanao State University'}
            </ThemedText>
          </View>
        </Card>

        {/* Settings Button */}
        <TouchableOpacity
          style={[styles.settingsButton, { backgroundColor: colors.surface }]}
          onPress={() => router.push('/(app)/settings')}
        >
          <View style={styles.settingsLeft}>
            <Ionicons name="settings-outline" size={22} color={colors.text} />
            <ThemedText variant="headline" style={styles.settingsText}>
              Settings
            </ThemedText>
          </View>
          <Ionicons
            name="chevron-forward"
            size={20}
            color={colors.textTertiary}
          />
        </TouchableOpacity>

        {/* Menu Sections */}
        {menuSections.map((section, sectionIndex) => (
          <View key={sectionIndex} style={styles.menuSection}>
            <ThemedText variant="footnote" color="secondary" style={styles.sectionTitle}>
              {section.title.toUpperCase()}
            </ThemedText>
            <Card variant="elevated">
              {section.items.map((item, itemIndex) => (
                <TouchableOpacity
                  key={itemIndex}
                  style={[
                    styles.menuItem,
                    itemIndex !== section.items.length - 1 && {
                      borderBottomWidth: StyleSheet.hairlineWidth,
                      borderBottomColor: colors.divider,
                    },
                  ]}
                  onPress={() => handleMenuPress(item.route)}
                >
                  <View style={styles.menuLeft}>
                    <Ionicons
                      name={item.icon as any}
                      size={22}
                      color={colors.text}
                    />
                    <ThemedText variant="body" style={styles.menuLabel}>
                      {item.label}
                    </ThemedText>
                  </View>
                  <Ionicons
                    name="chevron-forward"
                    size={18}
                    color={colors.textTertiary}
                  />
                </TouchableOpacity>
              ))}
            </Card>
          </View>
        ))}

        {/* Logout Button */}
        <TouchableOpacity
          style={[styles.logoutButton, { backgroundColor: colors.errorLight }]}
          onPress={handleLogout}
        >
          <Ionicons name="log-out-outline" size={22} color={colors.error} />
          <ThemedText
            variant="headline"
            style={[styles.logoutText, { color: colors.error }]}
          >
            Logout
          </ThemedText>
        </TouchableOpacity>

        {/* App Version */}
        <ThemedText
          variant="caption1"
          color="muted"
          align="center"
          style={styles.version}
        >
          WMSU Grading System v1.0.0
        </ThemedText>

        <View style={{ height: Layout.spacing.xl }} />
      </ScrollView>
    </ThemedView>
  );
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
  },
  scrollView: {
    flex: 1,
  },
  scrollContent: {
    paddingHorizontal: Layout.padding.horizontal,
  },
  header: {
    alignItems: 'center',
    marginBottom: Layout.spacing.lg,
  },
  name: {
    marginTop: Layout.spacing.md,
    marginBottom: Layout.spacing.xs,
  },
  infoCard: {
    marginBottom: Layout.spacing.md,
  },
  infoRow: {
    flexDirection: 'row',
    padding: Layout.spacing.md,
  },
  infoItem: {
    flex: 1,
    flexDirection: 'row',
    alignItems: 'center',
  },
  infoText: {
    marginLeft: Layout.spacing.sm,
    flex: 1,
  },
  infoDivider: {
    width: 1,
    marginHorizontal: Layout.spacing.md,
  },
  divider: {
    height: StyleSheet.hairlineWidth,
  },
  collegeInfo: {
    flexDirection: 'row',
    alignItems: 'center',
    padding: Layout.spacing.md,
  },
  collegeName: {
    marginLeft: Layout.spacing.sm,
    flex: 1,
  },
  settingsButton: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    padding: Layout.spacing.md,
    borderRadius: Layout.borderRadius.md,
    marginBottom: Layout.spacing.lg,
  },
  settingsLeft: {
    flexDirection: 'row',
    alignItems: 'center',
  },
  settingsText: {
    marginLeft: Layout.spacing.md,
  },
  menuSection: {
    marginBottom: Layout.spacing.lg,
  },
  sectionTitle: {
    marginBottom: Layout.spacing.sm,
    marginLeft: Layout.spacing.xs,
  },
  menuItem: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    padding: Layout.spacing.md,
  },
  menuLeft: {
    flexDirection: 'row',
    alignItems: 'center',
  },
  menuLabel: {
    marginLeft: Layout.spacing.md,
  },
  logoutButton: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    padding: Layout.spacing.md,
    borderRadius: Layout.borderRadius.md,
    marginBottom: Layout.spacing.md,
  },
  logoutText: {
    marginLeft: Layout.spacing.sm,
  },
  version: {
    marginTop: Layout.spacing.sm,
  },
});
