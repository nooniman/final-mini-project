/**
 * Settings Screen
 * App settings including theme toggle
 */

import React from 'react';
import {
  View,
  StyleSheet,
  ScrollView,
  TouchableOpacity,
  Switch,
  Alert,
} from 'react-native';
import { router } from 'expo-router';
import { useSafeAreaInsets } from 'react-native-safe-area-context';
import { Ionicons } from '@expo/vector-icons';
import { useTheme } from '../../src/context/ThemeContext';
import { useSettingsStore } from '../../src/store/useSettingsStore';
import { Layout } from '../../src/constants/Layout';
import {
  ThemedView,
  ThemedText,
  Card,
  Header,
} from '../../src/components/ui';
import type { ThemeMode } from '../../src/types/common';

export default function SettingsScreen() {
  const { colors, themeMode, setThemeMode, isDark } = useTheme();
  const insets = useSafeAreaInsets();
  const { 
    notificationSettings, 
    updateNotificationSettings,
    hapticFeedback,
    setHapticFeedback,
  } = useSettingsStore();

  const themeOptions: { label: string; value: ThemeMode; icon: string }[] = [
    { label: 'Light', value: 'light', icon: 'sunny-outline' },
    { label: 'Dark', value: 'dark', icon: 'moon-outline' },
    { label: 'System', value: 'system', icon: 'phone-portrait-outline' },
  ];

  const handleClearCache = () => {
    Alert.alert(
      'Clear Cache',
      'This will clear all cached data. You may need to re-login.',
      [
        { text: 'Cancel', style: 'cancel' },
        { text: 'Clear', style: 'destructive', onPress: () => {
          // Clear cache logic
          Alert.alert('Success', 'Cache cleared successfully');
        }},
      ]
    );
  };

  return (
    <ThemedView style={styles.container}>
      <Header
        title="Settings"
        showBack
        onBackPress={() => router.back()}
      />

      <ScrollView
        style={styles.scrollView}
        contentContainerStyle={[
          styles.scrollContent,
          { paddingBottom: insets.bottom + Layout.spacing.xl },
        ]}
        showsVerticalScrollIndicator={false}
      >
        {/* Appearance Section */}
        <View style={styles.section}>
          <ThemedText variant="footnote" color="secondary" style={styles.sectionTitle}>
            APPEARANCE
          </ThemedText>
          
          <Card variant="elevated">
            <View style={styles.settingItem}>
              <View style={styles.settingInfo}>
                <Ionicons name="color-palette-outline" size={22} color={colors.text} />
                <ThemedText variant="body" style={styles.settingLabel}>
                  Theme
                </ThemedText>
              </View>
            </View>

            <View style={styles.themeOptions}>
              {themeOptions.map((option) => (
                <TouchableOpacity
                  key={option.value}
                  style={[
                    styles.themeOption,
                    themeMode === option.value && {
                      backgroundColor: colors.primaryMuted,
                      borderColor: colors.primary,
                    },
                    { borderColor: colors.border },
                  ]}
                  onPress={() => setThemeMode(option.value)}
                >
                  <Ionicons
                    name={option.icon as any}
                    size={24}
                    color={themeMode === option.value ? colors.primary : colors.textSecondary}
                  />
                  <ThemedText
                    variant="caption1"
                    color={themeMode === option.value ? 'accent' : 'secondary'}
                    style={styles.themeLabel}
                  >
                    {option.label}
                  </ThemedText>
                  {themeMode === option.value && (
                    <View style={[styles.checkMark, { backgroundColor: colors.primary }]}>
                      <Ionicons name="checkmark" size={12} color="#FFFFFF" />
                    </View>
                  )}
                </TouchableOpacity>
              ))}
            </View>
          </Card>
        </View>

        {/* Notifications Section */}
        <View style={styles.section}>
          <ThemedText variant="footnote" color="secondary" style={styles.sectionTitle}>
            NOTIFICATIONS
          </ThemedText>
          
          <Card variant="elevated">
            <View style={[styles.settingItem, styles.settingRow]}>
              <View style={styles.settingInfo}>
                <Ionicons name="notifications-outline" size={22} color={colors.text} />
                <View style={styles.settingTextGroup}>
                  <ThemedText variant="body">Push Notifications</ThemedText>
                  <ThemedText variant="caption1" color="secondary">
                    Receive push notifications
                  </ThemedText>
                </View>
              </View>
              <Switch
                value={notificationSettings.pushEnabled}
                onValueChange={(value) => updateNotificationSettings({ pushEnabled: value })}
                trackColor={{ false: colors.border, true: colors.primaryLight }}
                thumbColor="#FFFFFF"
              />
            </View>

            <View style={[styles.divider, { backgroundColor: colors.divider }]} />

            <View style={[styles.settingItem, styles.settingRow]}>
              <View style={styles.settingInfo}>
                <Ionicons name="school-outline" size={22} color={colors.text} />
                <View style={styles.settingTextGroup}>
                  <ThemedText variant="body">Grade Alerts</ThemedText>
                  <ThemedText variant="caption1" color="secondary">
                    When new grades are posted
                  </ThemedText>
                </View>
              </View>
              <Switch
                value={notificationSettings.gradePosted}
                onValueChange={(value) => updateNotificationSettings({ gradePosted: value })}
                trackColor={{ false: colors.border, true: colors.primaryLight }}
                thumbColor="#FFFFFF"
              />
            </View>

            <View style={[styles.divider, { backgroundColor: colors.divider }]} />

            <View style={[styles.settingItem, styles.settingRow]}>
              <View style={styles.settingInfo}>
                <Ionicons name="calendar-outline" size={22} color={colors.text} />
                <View style={styles.settingTextGroup}>
                  <ThemedText variant="body">Attendance Alerts</ThemedText>
                  <ThemedText variant="caption1" color="secondary">
                    Low attendance warnings
                  </ThemedText>
                </View>
              </View>
              <Switch
                value={notificationSettings.attendanceAlert}
                onValueChange={(value) => updateNotificationSettings({ attendanceAlert: value })}
                trackColor={{ false: colors.border, true: colors.primaryLight }}
                thumbColor="#FFFFFF"
              />
            </View>

            <View style={[styles.divider, { backgroundColor: colors.divider }]} />

            <View style={[styles.settingItem, styles.settingRow]}>
              <View style={styles.settingInfo}>
                <Ionicons name="megaphone-outline" size={22} color={colors.text} />
                <View style={styles.settingTextGroup}>
                  <ThemedText variant="body">Announcements</ThemedText>
                  <ThemedText variant="caption1" color="secondary">
                    University announcements
                  </ThemedText>
                </View>
              </View>
              <Switch
                value={notificationSettings.announcements}
                onValueChange={(value) => updateNotificationSettings({ announcements: value })}
                trackColor={{ false: colors.border, true: colors.primaryLight }}
                thumbColor="#FFFFFF"
              />
            </View>
          </Card>
        </View>

        {/* General Section */}
        <View style={styles.section}>
          <ThemedText variant="footnote" color="secondary" style={styles.sectionTitle}>
            GENERAL
          </ThemedText>
          
          <Card variant="elevated">
            <View style={[styles.settingItem, styles.settingRow]}>
              <View style={styles.settingInfo}>
                <Ionicons name="pulse-outline" size={22} color={colors.text} />
                <View style={styles.settingTextGroup}>
                  <ThemedText variant="body">Haptic Feedback</ThemedText>
                  <ThemedText variant="caption1" color="secondary">
                    Vibration on interactions
                  </ThemedText>
                </View>
              </View>
              <Switch
                value={hapticFeedback}
                onValueChange={setHapticFeedback}
                trackColor={{ false: colors.border, true: colors.primaryLight }}
                thumbColor="#FFFFFF"
              />
            </View>
          </Card>
        </View>

        {/* Data Section */}
        <View style={styles.section}>
          <ThemedText variant="footnote" color="secondary" style={styles.sectionTitle}>
            DATA
          </ThemedText>
          
          <Card variant="elevated">
            <TouchableOpacity 
              style={[styles.settingItem, styles.settingRow]}
              onPress={handleClearCache}
            >
              <View style={styles.settingInfo}>
                <Ionicons name="trash-outline" size={22} color={colors.error} />
                <ThemedText variant="body" style={[styles.settingLabel, { color: colors.error }]}>
                  Clear Cache
                </ThemedText>
              </View>
              <Ionicons name="chevron-forward" size={18} color={colors.textTertiary} />
            </TouchableOpacity>
          </Card>
        </View>

        {/* About Section */}
        <View style={styles.section}>
          <ThemedText variant="footnote" color="secondary" style={styles.sectionTitle}>
            ABOUT
          </ThemedText>
          
          <Card variant="elevated">
            <View style={styles.settingItem}>
              <View style={styles.settingInfo}>
                <Ionicons name="information-circle-outline" size={22} color={colors.text} />
                <ThemedText variant="body" style={styles.settingLabel}>
                  Version
                </ThemedText>
              </View>
              <ThemedText variant="body" color="secondary">
                1.0.0
              </ThemedText>
            </View>

            <View style={[styles.divider, { backgroundColor: colors.divider }]} />

            <TouchableOpacity style={styles.settingItem}>
              <View style={styles.settingInfo}>
                <Ionicons name="document-text-outline" size={22} color={colors.text} />
                <ThemedText variant="body" style={styles.settingLabel}>
                  Terms of Service
                </ThemedText>
              </View>
              <Ionicons name="chevron-forward" size={18} color={colors.textTertiary} />
            </TouchableOpacity>

            <View style={[styles.divider, { backgroundColor: colors.divider }]} />

            <TouchableOpacity style={styles.settingItem}>
              <View style={styles.settingInfo}>
                <Ionicons name="shield-checkmark-outline" size={22} color={colors.text} />
                <ThemedText variant="body" style={styles.settingLabel}>
                  Privacy Policy
                </ThemedText>
              </View>
              <Ionicons name="chevron-forward" size={18} color={colors.textTertiary} />
            </TouchableOpacity>
          </Card>
        </View>
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
    paddingTop: Layout.spacing.md,
  },
  section: {
    marginBottom: Layout.spacing.lg,
  },
  sectionTitle: {
    marginBottom: Layout.spacing.sm,
    marginLeft: Layout.spacing.xs,
  },
  settingItem: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    padding: Layout.spacing.md,
  },
  settingRow: {},
  settingInfo: {
    flexDirection: 'row',
    alignItems: 'center',
    flex: 1,
  },
  settingLabel: {
    marginLeft: Layout.spacing.md,
  },
  settingTextGroup: {
    marginLeft: Layout.spacing.md,
    flex: 1,
  },
  divider: {
    height: StyleSheet.hairlineWidth,
    marginLeft: 54,
  },
  themeOptions: {
    flexDirection: 'row',
    padding: Layout.spacing.md,
    paddingTop: 0,
    gap: Layout.spacing.sm,
  },
  themeOption: {
    flex: 1,
    alignItems: 'center',
    padding: Layout.spacing.md,
    borderRadius: Layout.borderRadius.md,
    borderWidth: 1.5,
    position: 'relative',
  },
  themeLabel: {
    marginTop: Layout.spacing.xs,
  },
  checkMark: {
    position: 'absolute',
    top: 8,
    right: 8,
    width: 18,
    height: 18,
    borderRadius: 9,
    alignItems: 'center',
    justifyContent: 'center',
  },
});
