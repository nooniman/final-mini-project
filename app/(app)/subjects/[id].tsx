/**
 * Subject Detail Screen
 * Shows detailed information about a specific subject
 */

import React, { useEffect, useState } from 'react';
import { View, StyleSheet, ScrollView, ActivityIndicator } from 'react-native';
import { useLocalSearchParams, router } from 'expo-router';
import { Ionicons } from '@expo/vector-icons';
import { useTheme } from '../../../src/context/ThemeContext';
import { Layout } from '../../../src/constants/Layout';
import { subjectsApi } from '../../../src/services/api';
import type { SubjectDetails } from '../../../src/types/subjects';
import {
  ThemedView,
  ThemedText,
  Card,
  Badge,
  Header,
} from '../../../src/components/ui';

export default function SubjectDetailScreen() {
  const { colors } = useTheme();
  const { id } = useLocalSearchParams<{ id: string }>();
  const [subject, setSubject] = useState<SubjectDetails | null>(null);
  const [isLoading, setIsLoading] = useState(true);

  useEffect(() => {
    const fetchSubjectDetails = async () => {
      try {
        setIsLoading(true);
        const response = await subjectsApi.getById(id);
        if (response.success && response.data) {
          setSubject(response.data);
        }
      } catch (error) {
        console.error('Failed to fetch subject details:', error);
      } finally {
        setIsLoading(false);
      }
    };

    if (id) {
      fetchSubjectDetails();
    }
  }, [id]);

  if (isLoading) {
    return (
      <ThemedView style={styles.container}>
        <Header title="Subject Details" showBack />
        <View style={styles.loadingContainer}>
          <ActivityIndicator size="large" color={colors.primary} />
        </View>
      </ThemedView>
    );
  }

  if (!subject) {
    return (
      <ThemedView style={styles.container}>
        <Header title="Subject Details" showBack />
        <View style={styles.emptyContainer}>
          <Ionicons name="alert-circle-outline" size={64} color={colors.textSecondary} />
          <ThemedText variant="headline" style={{ color: colors.textSecondary, marginTop: 16 }}>
            Subject not found
          </ThemedText>
        </View>
      </ThemedView>
    );
  }

  return (
    <ThemedView style={styles.container}>
      <Header
        title="Subject Details"
        showBack
        onBackPress={() => router.back()}
      />

      <ScrollView
        style={styles.scrollView}
        contentContainerStyle={styles.scrollContent}
        showsVerticalScrollIndicator={false}
      >
        {/* Subject Header Card */}
        <Card variant="elevated" style={styles.headerCard}>
          <View style={styles.subjectHeader}>
            <Badge label={subject.code} variant="primary" />
            <ThemedText variant="title2" style={styles.subjectName}>
              {subject.name}
            </ThemedText>
            <View style={styles.unitsBadge}>
              <ThemedText variant="subheadline" color="secondary">
                {subject.units} units {subject.lectureHours && subject.labHours ? `(${subject.lectureHours} lec, ${subject.labHours} lab)` : ''}
              </ThemedText>
            </View>
          </View>

          <View style={[styles.divider, { backgroundColor: colors.divider }]} />

          <View style={styles.statsRow}>
            <View style={styles.statItem}>
              <Ionicons name="people-outline" size={20} color={colors.primary} />
              <ThemedText variant="headline">{subject.enrolledCount || 0}</ThemedText>
              <ThemedText variant="caption2" color="secondary">Enrolled</ThemedText>
            </View>
            <View style={[styles.statDivider, { backgroundColor: colors.divider }]} />
            <View style={styles.statItem}>
              <Ionicons name="resize-outline" size={20} color={colors.primary} />
              <ThemedText variant="headline">{subject.classSize || 0}</ThemedText>
              <ThemedText variant="caption2" color="secondary">Capacity</ThemedText>
            </View>
          </View>
        </Card>

        {/* Description */}
        {subject.description && (
          <View style={styles.section}>
            <ThemedText variant="title3" style={styles.sectionTitle}>
              Description
            </ThemedText>
            <Card variant="elevated" padding="md">
              <ThemedText variant="body" color="secondary" style={styles.description}>
                {subject.description}
              </ThemedText>
            </Card>
          </View>
        )}

        {/* Schedule */}
        {(() => {
          const schedules = subject.schedules || subject.schedule || [];
          return schedules.length > 0 && (
          <View style={styles.section}>
            <ThemedText variant="title3" style={styles.sectionTitle}>
              Schedule
            </ThemedText>
            <Card variant="elevated">
              {schedules.map((sched: any, index: number) => (
              <View key={index}>
                <View style={styles.scheduleRow}>
                  <View style={[styles.dayBadge, { backgroundColor: colors.primaryMuted }]}>
                    <ThemedText variant="caption1" color="accent" weight="semibold">
                      {(sched.dayOfWeek || sched.day || '').substring(0, 3).toUpperCase()}
                    </ThemedText>
                  </View>
                  <View style={styles.scheduleInfo}>
                    <ThemedText variant="headline">
                      {sched.time || `${sched.startTime || ''} - ${sched.endTime || ''}`}
                    </ThemedText>
                    <View style={styles.scheduleDetails}>
                      <Ionicons name="location-outline" size={14} color={colors.textTertiary} />
                      <ThemedText variant="caption1" color="secondary">
                        {sched.room || 'TBA'}
                      </ThemedText>
                      {sched.type && (
                      <Badge
                        label={sched.type}
                        variant={sched.type === 'Laboratory' ? 'info' : 'secondary'}
                        size="sm"
                      />
                      )}
                    </View>
                  </View>
                </View>
                {index !== schedules.length - 1 && (
                  <View style={[styles.scheduleDivider, { backgroundColor: colors.divider }]} />
                )}
              </View>
            ))}
          </Card>
        </View>
          );
        })()}

        {/* Instructor */}
        <View style={styles.section}>
          <ThemedText variant="title3" style={styles.sectionTitle}>
            Instructor
          </ThemedText>
          <Card variant="elevated" padding="md">
            <View style={styles.instructorHeader}>
              <View style={[styles.instructorAvatar, { backgroundColor: colors.primaryMuted }]}>
                <Ionicons name="person" size={28} color={colors.primary} />
              </View>
              <View style={styles.instructorInfo}>
                <ThemedText variant="headline">{subject.instructor?.name || 'TBA'}</ThemedText>
                <ThemedText variant="subheadline" color="secondary">
                  {subject.instructor?.department || 'Department'}
                </ThemedText>
              </View>
            </View>

            <View style={[styles.divider, { backgroundColor: colors.divider, marginVertical: Layout.spacing.md }]} />

            <View style={styles.contactInfo}>
              <View style={styles.contactRow}>
                <Ionicons name="mail-outline" size={18} color={colors.textTertiary} />
                <ThemedText variant="subheadline" color="secondary">
                  {subject.instructor?.email || 'N/A'}
                </ThemedText>
              </View>
              <View style={styles.contactRow}>
                <Ionicons name="location-outline" size={18} color={colors.textTertiary} />
                <ThemedText variant="subheadline" color="secondary">
                  {subject.instructor?.title || 'Faculty Office'}
                </ThemedText>
              </View>
              {subject.instructor?.email && (
                <View style={styles.contactRow}>
                  <Ionicons name="time-outline" size={18} color={colors.textTertiary} />
                  <ThemedText variant="subheadline" color="secondary">
                    Consultation Hours
                  </ThemedText>
                </View>
              )}
            </View>
          </Card>
        </View>

        {/* Prerequisites */}
        {subject.prerequisites && subject.prerequisites.length > 0 && (
          <View style={styles.section}>
            <ThemedText variant="title3" style={styles.sectionTitle}>
              Prerequisites
            </ThemedText>
            <Card variant="elevated">
              {subject.prerequisites.map((prereq: string, index: number) => (
                <View key={index}>
                  <View style={styles.prereqRow}>
                    <Ionicons name="checkmark-circle" size={20} color={colors.success} />
                    <ThemedText variant="body" style={styles.prereqText}>
                      {prereq}
                    </ThemedText>
                  </View>
                  {index !== (subject.prerequisites?.length ?? 0) - 1 && (
                    <View style={[styles.prereqDivider, { backgroundColor: colors.divider }]} />
                  )}
                </View>
              ))}
            </Card>
          </View>
        )}

        <View style={{ height: Layout.spacing.xl }} />
      </ScrollView>
    </ThemedView>
  );
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
  },
  loadingContainer: {
    flex: 1,
    alignItems: 'center',
    justifyContent: 'center',
  },
  emptyContainer: {
    flex: 1,
    alignItems: 'center',
    justifyContent: 'center',
    paddingVertical: 80,
  },
  scrollView: {
    flex: 1,
  },
  scrollContent: {
    paddingHorizontal: Layout.padding.horizontal,
    paddingTop: Layout.spacing.md,
  },
  headerCard: {
    marginBottom: Layout.spacing.lg,
  },
  subjectHeader: {
    padding: Layout.spacing.md,
  },
  subjectName: {
    marginTop: Layout.spacing.sm,
    marginBottom: Layout.spacing.xs,
  },
  unitsBadge: {},
  divider: {
    height: StyleSheet.hairlineWidth,
  },
  statsRow: {
    flexDirection: 'row',
  },
  statItem: {
    flex: 1,
    alignItems: 'center',
    paddingVertical: Layout.spacing.md,
    gap: 2,
  },
  statDivider: {
    width: 1,
  },
  section: {
    marginBottom: Layout.spacing.lg,
  },
  sectionTitle: {
    marginBottom: Layout.spacing.sm,
  },
  description: {
    lineHeight: 24,
  },
  scheduleRow: {
    flexDirection: 'row',
    alignItems: 'center',
    padding: Layout.spacing.md,
  },
  dayBadge: {
    width: 48,
    height: 48,
    borderRadius: 8,
    alignItems: 'center',
    justifyContent: 'center',
    marginRight: Layout.spacing.md,
  },
  scheduleInfo: {
    flex: 1,
  },
  scheduleDetails: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 4,
    marginTop: 4,
  },
  scheduleDivider: {
    height: StyleSheet.hairlineWidth,
    marginLeft: 76,
  },
  instructorHeader: {
    flexDirection: 'row',
    alignItems: 'center',
  },
  instructorAvatar: {
    width: 56,
    height: 56,
    borderRadius: 28,
    alignItems: 'center',
    justifyContent: 'center',
    marginRight: Layout.spacing.md,
  },
  instructorInfo: {
    flex: 1,
  },
  contactInfo: {
    gap: Layout.spacing.sm,
  },
  contactRow: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: Layout.spacing.sm,
  },
  prereqRow: {
    flexDirection: 'row',
    alignItems: 'center',
    padding: Layout.spacing.md,
  },
  prereqText: {
    marginLeft: Layout.spacing.sm,
    flex: 1,
  },
  prereqDivider: {
    height: StyleSheet.hairlineWidth,
    marginLeft: 44,
  },
});
